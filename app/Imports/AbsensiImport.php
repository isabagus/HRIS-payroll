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
    public $skippedCount = 0;
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
        $karyawan = null;

        $namaTrim = trim((string)($namaHeader ?? ''));
        $nikTrim  = trim((string)($nikHeader ?? ''));

        // Parse ID/NIK if header contains "/" (e.g. "0000000000000002 / 3201010101010002")
        // NOTE: Format in data-absensi is "20/20" where both values can be the same (Kode Karyawan)
        $idMesin = '';
        $nikKtp  = '';
        if (!empty($nikTrim)) {
            $parts   = explode('/', $nikTrim);
            $idMesin = trim($parts[0] ?? '');
            $nikKtp  = trim($parts[1] ?? '');
        }

        // 1. Try Exact Match by NIK KTP (only if it's a proper NIK, i.e. length >= 10)
        if (!empty($nikKtp) && strlen($nikKtp) >= 10) {
            $karyawan = Karyawan::where('nik', $nikKtp)->first();
        }

        // 2. Try Exact Match by Kode Karyawan (idMesin)
        if (!$karyawan && !empty($idMesin)) {
            $karyawan = Karyawan::where('Kode_karyawan', $idMesin)->first();
        }

        // 3. Also try nikKtp as Kode Karyawan (in case format is kode/kode)
        if (!$karyawan && !empty($nikKtp)) {
            $karyawan = Karyawan::where('Kode_karyawan', $nikKtp)->first();
        }

        // 4. Try Exact Match by Full Name (case-insensitive, exact string match)
        if (!$karyawan && !empty($namaTrim)) {
            $karyawan = Karyawan::whereRaw('LOWER(TRIM(nama_karyawan)) = ?', [strtolower($namaTrim)])->first();
        }

        // 5. Fuzzy Name Match: try LIKE match if exact fails
        if (!$karyawan && !empty($namaTrim)) {
            $karyawan = Karyawan::whereRaw('LOWER(nama_karyawan) LIKE ?', ['%' . strtolower($namaTrim) . '%'])->first();
        }

        // 6. Secondary check for NIK stored in different column
        if (!$karyawan && !empty($idMesin)) {
            $karyawan = Karyawan::where('nik', $idMesin)->first();
        }

        // STRICT VALIDATION: If employee is NOT found, reject import with clear error message!
        // DO NOT use fallback to any dummy or first employee in database!
        if (!$karyawan) {
            $label = $namaTrim ?: ($nikKtp ?: $idMesin ?: 'Tanpa Nama');
            $this->importErrors[] = "Karyawan '{$label}' tidak ditemukan di database. Mohon daftarkan data karyawan '{$label}' terlebih dahulu sebelum mengimpor absensinya.";
            $this->skippedCount++;
            return;
        }

        // Rows start around index 10 (Row 11 in 1-based index)
        foreach ($rows as $index => $row) {
            if ($index < 10) continue;

            $col0 = trim((string)($row[0] ?? ''));
            if (empty($col0) || strtoupper($col0) === 'TOTAL') {
                continue;
            }

            try {
                $tanggalStr = $col0;
                if (str_contains($tanggalStr, ',')) {
                    $parts = explode(',', $tanggalStr);
                    $tanggalStr = trim($parts[1] ?? $parts[0]);
                }

                // Translate Indonesian month names before parsing
                $tanggalStr = $this->translateIndonesianMonth($tanggalStr);

                $tanggal = $this->parseExcelDate($tanggalStr);
                if (!$tanggal) continue;

                $jamKerjaMasuk  = trim((string)($row[1] ?? ''));
                $jamKerjaPulang = trim((string)($row[2] ?? ''));
                $durasiKerja    = trim((string)($row[3] ?? ''));
                $jamMasuk       = trim((string)($row[4] ?? ''));
                $jamPulang      = trim((string)($row[5] ?? ''));
                $terlambat      = trim((string)($row[6] ?? ''));
                $pulangCepat    = trim((string)($row[7] ?? ''));
                $totalJamKerja  = trim((string)($row[8] ?? ''));
                $istirahat      = trim((string)($row[9] ?? ''));
                $lemburAwal     = trim((string)($row[10] ?? ''));
                $lemburAkhir    = trim((string)($row[11] ?? ''));
                $totalLembur    = trim((string)($row[12] ?? ''));
                $masukKerja     = trim((string)($row[13] ?? '1'));
                $keterangan     = trim((string)($row[14] ?? '-'));

                // Determine status absensi automatically based on jam_masuk and keterangan
                $statusAbsensi = 'Hadir';
                $keteranganLower = strtolower(trim($keterangan));
                if ($masukKerja === '0' || strtolower($keterangan) === 'libur' || $jamKerjaMasuk === '-') {
                    $statusAbsensi = (strtolower($keterangan) === 'libur') ? 'Libur' : 'Alpha';
                } elseif ($keteranganLower === 'setengah hari') {
                    // Explicit keterangan 'setengah hari' → langsung set Setengah Hari
                    $statusAbsensi = 'Setengah Hari';
                } elseif (in_array($keteranganLower, ['izin', 'sakit', 'alpha'])) {
                    $statusAbsensi = ucfirst($keteranganLower);
                } else {
                    $valJamMasuk = ($jamMasuk !== '-' && !empty($jamMasuk)) ? $jamMasuk : null;
                    $statusAbsensi = AbsensiController::determineStatus($valJamMasuk, null, $keterangan);
                }

                // Update or Create
                Absensi::updateOrCreate(
                    [
                        'ID_karyawan' => $karyawan->ID_karyawan,
                        'date'        => $tanggal,
                    ],
                    [
                        'jam_kerja_masuk'  => $jamKerjaMasuk !== '-' ? $jamKerjaMasuk : null,
                        'jam_kerja_pulang' => $jamKerjaPulang !== '-' ? $jamKerjaPulang : null,
                        'durasi_kerja'     => $durasiKerja !== '-' ? $durasiKerja : null,
                        'jam_masuk'        => $jamMasuk !== '-' ? $jamMasuk : null,
                        'jam_pulang'       => $jamPulang !== '-' ? $jamPulang : null,
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
                $this->importErrors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
            }
        }
    }

    private function processStandardListFormat(Collection $rows)
    {
        $header = $rows->first()->map(fn($v) => strtolower(trim((string)$v)))->toArray();
        $rowsData = $rows->slice(1);

        foreach ($rowsData as $index => $row) {
            try {
                $rowArray = $row->toArray();
                $data = [];
                foreach ($header as $colIdx => $colName) {
                    $data[$colName] = trim((string)($rowArray[$colIdx] ?? ''));
                }

                $kode = $data['kode_karyawan'] ?? $data['kode'] ?? '';
                $nama = $data['nama_karyawan'] ?? $data['nama'] ?? '';

                if (empty($kode) && empty($nama)) continue;

                $karyawan = null;

                // 1. Exact match Kode Karyawan
                if (!empty($kode)) {
                    $karyawan = Karyawan::where('Kode_karyawan', $kode)->first();
                }

                // 2. Exact match Full Name
                if (!$karyawan && !empty($nama)) {
                    $karyawan = Karyawan::whereRaw('LOWER(TRIM(nama_karyawan)) = ?', [strtolower(trim($nama))])->first();
                }

                // STRICT VALIDATION
                if (!$karyawan) {
                    $this->skippedCount++;
                    $label = $nama ?: $kode;
                    $this->importErrors[] = "Baris " . ($index + 2) . ": Karyawan '{$label}' tidak ditemukan di database.";
                    continue;
                }

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
                        'jam_masuk'      => $data['jam_masuk'] ?? null,
                        'jam_pulang'     => $data['jam_pulang'] ?? null,
                        'status_absensi' => $status,
                        'keterangan'     => $data['keterangan'] ?? '-',
                    ]
                );

                $this->importedCount++;
            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->importErrors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }
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
