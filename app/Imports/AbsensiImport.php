<?php

namespace App\Imports;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Http\Controllers\AbsensiController;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AbsensiImport implements ToCollection
{
    public $importedCount = 0;
    public $skippedCount  = 0;

    /**
     * Detail setiap baris yang GAGAL diimport.
     * Setiap item:
     *   ['baris' => int|null, 'id' => string, 'nama' => string, 'alasan' => string]
     */
    public $failedRows   = [];

    // Dipertahankan untuk kompatibilitas tampilan lama
    public $importErrors = [];

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // Check if format is report style (test_absensi.xlsx)
        $namaKaryawanFromHeader = null;
        $nikFromHeader = null;

        foreach ($rows->take(10) as $rowIndex => $row) {
            $colA = strtolower(trim((string)($row[0] ?? '')));
            if ($colA === 'nama karyawan') {
                $namaKaryawanFromHeader = trim((string)($row[1] ?? ''));
            }
            if ($colA === 'id/nik') {
                $nikFromHeader = trim((string)($row[1] ?? ''));
            }
        }

        if ($namaKaryawanFromHeader || $nikFromHeader) {
            // Processing Report Style (test_absensi.xlsx)
            $this->processReportStyleFormat($rows, $namaKaryawanFromHeader, $nikFromHeader);
        } else {
            // Processing Standard List Format
            $this->processStandardListFormat($rows);
        }
    }

    /**
     * Convert Indonesian month names to English for Carbon parsing.
     */
    private function translateIndonesianMonth(string $str): string
    {
        $bulanId = [
            'januari' => 'January', 'februari' => 'February', 'maret' => 'March',
            'april'   => 'April',   'mei'       => 'May',      'juni'  => 'June',
            'juli'    => 'July',    'agustus'   => 'August',   'september' => 'September',
            'oktober' => 'October', 'november'  => 'November', 'desember'  => 'December',
        ];
        return str_ireplace(array_keys($bulanId), array_values($bulanId), $str);
    }

    private function processReportStyleFormat(Collection $rows, $namaHeader, $nikHeader)
    {
        $namaTrim = trim((string) ($namaHeader ?? ''));
        $nikTrim  = trim((string) ($nikHeader  ?? ''));

        // Parse ID/NIK jika header berisi "/" (mis. "20/20" atau "kode/nik")
        $idMesin = '';
        $nikKtp  = '';
        if (!empty($nikTrim)) {
            $parts   = explode('/', $nikTrim);
            $idMesin = trim($parts[0] ?? '');
            $nikKtp  = trim($parts[1] ?? '');
        }

        // --- Langkah 1: Temukan karyawan berdasarkan ID (Kode / NIK) ---
        $karyawan = null;

        // 1a. NIK KTP (panjang >= 10)
        if (!empty($nikKtp) && strlen($nikKtp) >= 10) {
            $karyawan = Karyawan::where('nik', $nikKtp)->first();
        }

        // 1b. Kode Karyawan dari idMesin
        if (!$karyawan && !empty($idMesin)) {
            $karyawan = Karyawan::where('Kode_karyawan', $idMesin)->first();
        }

        // 1c. Kode Karyawan dari nikKtp (format kode/kode)
        if (!$karyawan && !empty($nikKtp)) {
            $karyawan = Karyawan::where('Kode_karyawan', $nikKtp)->first();
        }

        // 1d. Coba NIK dari kolom nik database dengan idMesin
        if (!$karyawan && !empty($idMesin)) {
            $karyawan = Karyawan::where('nik', $idMesin)->first();
        }

        // --- Langkah 2: Jika ID ditemukan, validasi nama karyawan di file vs database ---
        if ($karyawan && !empty($namaTrim)) {
            $namaDB   = strtolower(trim($karyawan->nama_karyawan ?? ''));
            $namaFile = strtolower($namaTrim);

            if ($namaDB !== $namaFile) {
                // ID ditemukan tapi nama tidak cocok → tolak seluruh file ini
                $idLabel = $nikTrim ?: $idMesin;
                $alasan  = "ID \"{$idLabel}\" terdaftar atas nama \"{$karyawan->nama_karyawan}\", tidak cocok dengan nama \"{$namaTrim}\" pada baris ini";

                $this->failedRows[] = [
                    'baris'  => null,
                    'id'     => $idLabel,
                    'nama'   => $namaTrim,
                    'alasan' => $alasan,
                ];
                $this->importErrors[] = "Header file — ID: {$idLabel}, Nama: {$namaTrim} — {$alasan}";
                $this->skippedCount++;
                return;
            }
        }

        // --- Langkah 3: Jika ID tidak ditemukan via kode/NIK, fallback via nama ---
        if (!$karyawan && !empty($namaTrim)) {
            $karyawan = Karyawan::whereRaw('LOWER(TRIM(nama_karyawan)) = ?', [strtolower($namaTrim)])->first();

            if (!$karyawan) {
                $karyawan = Karyawan::whereRaw('LOWER(nama_karyawan) LIKE ?', ['%' . strtolower($namaTrim) . '%'])->first();
            }
        }

        // --- Langkah 4: Tetap tidak ditemukan → tolak seluruh file ---
        if (!$karyawan) {
            $label  = $namaTrim ?: ($nikKtp ?: $idMesin ?: 'Tanpa Nama');
            $alasan = 'Karyawan tidak ditemukan di database. Daftarkan karyawan ini terlebih dahulu.';

            $this->failedRows[] = [
                'baris'  => null,
                'id'     => $nikTrim ?: $idMesin,
                'nama'   => $namaTrim,
                'alasan' => $alasan,
            ];
            $this->importErrors[] = "Header file — Nama: {$label} — {$alasan}";
            $this->skippedCount++;
            return;
        }

        // --- Langkah 5: Proses baris data absensi (mulai index 10 = baris 11 Excel) ---
        foreach ($rows as $index => $row) {
            if ($index < 10) {
                continue;
            }

            $col0 = trim((string) ($row[0] ?? ''));
            if (empty($col0) || strtoupper($col0) === 'TOTAL') {
                continue;
            }

            $excelRow = $index + 1;

            try {
                $tanggalStr = $col0;
                if (str_contains($tanggalStr, ',')) {
                    $parts      = explode(',', $tanggalStr);
                    $tanggalStr = trim($parts[1] ?? $parts[0]);
                }

                $tanggalStr = $this->translateIndonesianMonth($tanggalStr);
                $tanggal    = $this->parseExcelDate($tanggalStr);
                if (!$tanggal) {
                    continue;
                }

                $jamKerjaMasuk  = $this->parseExcelTime($row[1]  ?? null);
                $jamKerjaPulang = $this->parseExcelTime($row[2]  ?? null);
                $durasiKerja    = $this->parseExcelTime($row[3]  ?? null);
                $jamMasuk       = $this->parseExcelTime($row[4]  ?? null);
                $jamPulang      = $this->parseExcelTime($row[5]  ?? null);
                $terlambat      = $this->parseExcelTime($row[6]  ?? null) ?? '00:00';
                $pulangCepat    = $this->parseExcelTime($row[7]  ?? null) ?? '00:00';
                $totalJamKerja  = $this->parseExcelTime($row[8]  ?? null) ?? '00:00';
                $istirahat      = $this->parseExcelTime($row[9]  ?? null) ?? '01:00';
                $lemburAwal     = $this->parseExcelTime($row[10] ?? null) ?? '00:00';
                $lemburAkhir    = $this->parseExcelTime($row[11] ?? null) ?? '00:00';
                $totalLembur    = $this->parseExcelTime($row[12] ?? null) ?? '00:00';
                $masukKerja     = trim((string) ($row[13] ?? '1'));
                $keterangan     = trim((string) ($row[14] ?? '-'));

                $statusAbsensi   = 'Hadir';
                $keteranganLower = strtolower(trim($keterangan));

                if ($masukKerja === '0' || strtolower($keterangan) === 'libur' || empty($jamKerjaMasuk)) {
                    $statusAbsensi = (strtolower($keterangan) === 'libur') ? 'Libur' : 'Alpha';
                } elseif ($keteranganLower === 'setengah hari') {
                    $statusAbsensi = 'Setengah Hari';
                } elseif (in_array($keteranganLower, ['izin', 'sakit', 'alpha'])) {
                    $statusAbsensi = ucfirst($keteranganLower);
                } else {
                    $valJamMasuk   = (!empty($jamMasuk) && $jamMasuk !== '-') ? $jamMasuk : null;
                    $statusAbsensi = AbsensiController::determineStatus($valJamMasuk, null, $keterangan);
                }

                Absensi::updateOrCreate(
                    [
                        'ID_karyawan' => $karyawan->ID_karyawan,
                        'date'        => $tanggal,
                    ],
                    [
                        'jam_kerja_masuk'  => $jamKerjaMasuk,
                        'jam_kerja_pulang' => $jamKerjaPulang,
                        'durasi_kerja'     => $durasiKerja,
                        'jam_masuk'        => $jamMasuk,
                        'jam_pulang'       => $jamPulang,
                        'terlambat'        => $terlambat,
                        'pulang_cepat'     => $pulangCepat,
                        'total_jam_kerja'  => $totalJamKerja,
                        'istirahat'        => $istirahat,
                        'lembur_awal'      => $lemburAwal,
                        'lembur_akhir'     => $lemburAkhir,
                        'total_lembur'     => $totalLembur,
                        'masuk_kerja'      => $masukKerja,
                        'status_absensi'   => $statusAbsensi,
                        'keterangan'       => $keterangan,
                    ]
                );

                $this->importedCount++;
            } catch (\Exception $e) {
                $this->skippedCount++;
                $alasan = 'Error teknis: ' . $e->getMessage();

                $this->failedRows[] = [
                    'baris'  => $excelRow,
                    'id'     => $nikTrim ?: $idMesin,
                    'nama'   => $namaTrim,
                    'alasan' => $alasan,
                ];
                $this->importErrors[] = "Baris {$excelRow}: {$alasan}";
            }
        }
    }

    private function processStandardListFormat(Collection $rows)
    {
        $header   = $rows->first()->map(fn($v) => strtolower(trim((string) $v)))->toArray();
        $rowsData = $rows->slice(1);

        foreach ($rowsData as $index => $row) {
            // $index adalah key Collection (0-based dari awal). Karena slice(1), key 1 = baris Excel 2.
            $excelRow = $index + 1;

            try {
                $rowArray = $row->toArray();
                $data     = [];
                foreach ($header as $colIdx => $colName) {
                    $data[$colName] = trim((string) ($rowArray[$colIdx] ?? ''));
                }

                $kodeFile = $data['id_karyawan'] ?? $data['id'] ?? $data['kode_karyawan'] ?? $data['kode'] ?? '';
                $namaFile = $data['nama_karyawan'] ?? $data['nama'] ?? '';

                if (empty($kodeFile) && empty($namaFile)) {
                    continue;
                }

                // ------------------------------------------------------------------
                // VALIDASI KETAT PER BARIS (Logika AND: ID Harus Ada DAN Nama Harus Cocok):
                // Parameter 1: ID Karyawan pada baris Excel HARUS ditemukan di database.
                // Parameter 2: Nama Karyawan pada baris Excel HARUS SAMA PERSIS dengan nama pemilik ID tersebut.
                // ------------------------------------------------------------------
                $karyawan    = null;
                $alasanGagal = null;

                if (!empty($kodeFile)) {
                    $karyawan = Karyawan::where('Kode_karyawan', $kodeFile)
                        ->orWhere('nik', $kodeFile)
                        ->orWhere('ID_karyawan', $kodeFile)
                        ->first();
                }

                $isValid = ($karyawan !== null)
                    && !empty($namaFile)
                    && (trim(strtolower($karyawan->nama_karyawan)) === trim(strtolower($namaFile)));

                if (!$isValid) {
                    if (!$karyawan) {
                        $alasanGagal = "ID Karyawan \"{$kodeFile}\" tidak ditemukan di database";
                    } else {
                        $alasanGagal = "ID \"{$kodeFile}\" terdaftar atas nama \"{$karyawan->nama_karyawan}\", tidak cocok dengan nama \"{$namaFile}\" pada baris ini";
                    }

                    $this->skippedCount++;
                    $this->failedRows[] = [
                        'baris'  => $excelRow,
                        'id'     => $kodeFile ?: '-',
                        'nama'   => $namaFile ?: '-',
                        'alasan' => $alasanGagal,
                    ];
                    $this->importErrors[] = "Baris {$excelRow} — ID: \"{$kodeFile}\", Nama: \"{$namaFile}\" — {$alasanGagal}";
                    continue;
                }

                // Karyawan valid → proses simpan absensi
                $tanggal    = $this->parseExcelDate($data['tanggal'] ?? $data['date'] ?? date('Y-m-d'));
                $jamMasuk   = $data['jam_masuk'] ?? null;
                $userStatus = ucfirst(strtolower($data['status_absensi'] ?? $data['status'] ?? ''));
                $status     = AbsensiController::determineStatus($jamMasuk, $userStatus, $data['keterangan'] ?? null);

                Absensi::updateOrCreate(
                    [
                        'ID_karyawan' => $karyawan->ID_karyawan,
                        'date'        => $tanggal,
                    ],
                    [
                        'jam_masuk'      => $this->parseExcelTime($data['jam_masuk'] ?? null),
                        'jam_pulang'     => $this->parseExcelTime($data['jam_pulang'] ?? null),
                        'status_absensi' => $status,
                        'keterangan'     => $data['keterangan'] ?? '-',
                    ]
                );

                $this->importedCount++;
            } catch (\Exception $e) {
                $this->skippedCount++;
                $alasan   = 'Error teknis: ' . $e->getMessage();
                $kodeFile = $data['kode_karyawan'] ?? $data['kode'] ?? '';
                $namaFile = $data['nama_karyawan'] ?? $data['nama'] ?? '';

                $this->failedRows[] = [
                    'baris'  => $excelRow,
                    'id'     => $kodeFile,
                    'nama'   => $namaFile,
                    'alasan' => $alasan,
                ];
                $this->importErrors[] = "Baris {$excelRow}: {$alasan}";
            }
        }
    }

    private function parseExcelTime($value): ?string
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        $value = trim((string) $value);

        // Jika berupa pecahan desimal Excel (misal: 0.30902777777778)
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('H:i');
            } catch (\Exception $e) {
                $totalSeconds = round((float) $value * 86400);
                $hours = floor($totalSeconds / 3600) % 24;
                $minutes = floor(($totalSeconds % 3600) / 60);
                return sprintf('%02d:%02d', $hours, $minutes);
            }
        }

        // Jika string sudah ada format waktu (misal "07:25:00" atau "07:25")
        if (preg_match('/^(\d{1,2}):(\d{2})/', $value, $matches)) {
            return sprintf('%02d:%02d', $matches[1], $matches[2]);
        }

        return mb_substr($value, 0, 10);
    }

    private function parseExcelDate($value)
    {
        if (empty($value)) return date('Y-m-d');
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return date('Y-m-d');
        }
    }
}
