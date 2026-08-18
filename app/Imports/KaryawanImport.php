<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Status;
use App\Models\Instansi;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;

class KaryawanImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $skippedCount = 0;
    public $importErrors = [];

    public function headingRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $nama = trim($row['nama'] ?? $row['nama_karyawan'] ?? '');
                $kode = trim($row['id_karyawan'] ?? $row['kode_karyawan'] ?? '');

                if (empty($nama) && empty($kode)) {
                    continue;
                }

                $nik = $this->cleanNumericString($row['nik'] ?? $row['no_nik'] ?? null);
                $noHp = $this->cleanNumericString($row['no_telepon'] ?? $row['no_hp'] ?? $row['no_tlp'] ?? $row['telepon'] ?? $row['hp'] ?? null);

                // Divisi
                $namaDivisi = trim($row['devisi'] ?? $row['divisi'] ?? '');
                $divisi = null;
                if (!empty($namaDivisi)) {
                    $divisi = Divisi::where('nama_divisi', 'like', '%' . $namaDivisi . '%')->first();
                    if (!$divisi) {
                        $divisi = Divisi::create(['nama_divisi' => $namaDivisi]);
                    }
                }

                // Jabatan
                $namaJabatan = trim($row['jabatan'] ?? '');
                $jabatan = null;
                if (!empty($namaJabatan)) {
                    $jabatan = Jabatan::where('nama_jabatan', 'like', '%' . $namaJabatan . '%')->first();
                    if (!$jabatan) {
                        $jabatan = Jabatan::create(['nama_jabatan' => $namaJabatan]);
                    }
                }
                if (!$jabatan) {
                    $jabatan = Jabatan::first();
                    if (!$jabatan) {
                        $jabatan = Jabatan::create(['nama_jabatan' => 'Staff']);
                    }
                }

                // Instansi (Perusahaan)
                $namaInstansi = trim($row['perusahaan'] ?? $row['instansi'] ?? '');
                $instansi = null;
                if (!empty($namaInstansi)) {
                    $instansi = Instansi::where('nama_instansi', 'like', '%' . $namaInstansi . '%')->first();
                    if (!$instansi) {
                        $instansi = Instansi::create(['nama_instansi' => $namaInstansi]);
                    }
                }

                // Status
                $statusNama = trim($row['status'] ?? $row['status_karyawan'] ?? 'Karyawan Tetap');
                if (empty($statusNama) || strtolower($statusNama) === 'aktif') {
                    $statusNama = 'Karyawan Tetap';
                }
                $status = Status::where('nama_status', 'like', '%' . $statusNama . '%')->first();
                if (!$status) {
                    $status = Status::create(['nama_status' => $statusNama]);
                }

                // Parse Gender
                $jkRaw = strtoupper(trim($row['jenis_kelamin'] ?? ''));
                $jenisKelamin = 'L';
                if (in_array($jkRaw, ['PEREMPUAN', 'P', 'WANITA', 'FEMALE', 'F'])) {
                    $jenisKelamin = 'P';
                }

                // Parse Tanggal Lahir (TTL)
                $tanggalLahir = null;
                $ttlRaw = $row['ttl'] ?? $row['tanggal_lahir'] ?? null;
                if ($ttlRaw) {
                    $tanggalLahir = $this->parseExcelDate($ttlRaw);
                }

                // Parse Awal Masuk
                $awalMasuk = null;
                $masukRaw = $row['awal_masuk'] ?? $row['tgl_masuk'] ?? null;
                if ($masukRaw) {
                    $awalMasuk = $this->parseExcelDate($masukRaw);
                }

                $kodeKaryawan = !empty($kode) ? $kode : 'KRY-' . strtoupper(Str::random(6));

                // Check duplicate Kode Karyawan
                if (Karyawan::where('Kode_karyawan', $kodeKaryawan)->exists()) {
                    $existing = Karyawan::where('Kode_karyawan', $kodeKaryawan)->first();
                    $existing->update([
                        'nama_karyawan'       => $nama ?: $existing->nama_karyawan,
                        'email'               => trim($row['email'] ?? $existing->email),
                        'nik'                 => $nik ?: $existing->nik,
                        'no_tlp'              => $noHp ?: $existing->no_tlp,
                        'tempat_lahir'        => trim($row['tempat_lahir'] ?? $existing->tempat_lahir),
                        'tanggal_lahir'       => $tanggalLahir ?? $existing->tanggal_lahir,
                        'jenis_kelamin'       => $jenisKelamin,
                        'alamat'              => trim($row['alamat'] ?? $existing->alamat),
                        'pendidikan_terakhir' => trim($row['pendidikan_terakhir'] ?? $existing->pendidikan_terakhir),
                        'prodi'               => trim($row['prodi'] ?? $existing->prodi),
                        'tahun_lulus'         => trim($row['tahun_lulus'] ?? $existing->tahun_lulus),
                        'perusahaan'          => $namaInstansi ?: $existing->perusahaan,
                        'id_instansi'         => $instansi?->ID_instansi ?? $existing->id_instansi,
                        'awal_masuk'          => $awalMasuk ?? $existing->awal_masuk,
                        'tanggal_gajian'      => is_numeric($row['tanggal_gajian'] ?? null) ? (int)$row['tanggal_gajian'] : $existing->tanggal_gajian,
                        'id_divisi'           => $divisi?->ID_divisi ?? $existing->id_divisi,
                        'id_jabatan'          => $jabatan?->ID_jabatan ?? $existing->id_jabatan,
                        'id_status'           => $status?->ID_status ?? $existing->id_status,
                    ]);
                    $this->importedCount++;
                    continue;
                }

                Karyawan::create([
                    'Kode_karyawan'       => $kodeKaryawan,
                    'nama_karyawan'       => $nama,
                    'email'               => trim($row['email'] ?? ''),
                    'nik'                 => $nik,
                    'no_tlp'              => $noHp ?: '',
                    'tempat_lahir'        => trim($row['tempat_lahir'] ?? ''),
                    'tanggal_lahir'       => $tanggalLahir,
                    'jenis_kelamin'       => $jenisKelamin,
                    'alamat'              => trim($row['alamat'] ?? ''),
                    'pendidikan_terakhir' => trim($row['pendidikan_terakhir'] ?? ''),
                    'prodi'               => trim($row['prodi'] ?? ''),
                    'tahun_lulus'         => trim($row['tahun_lulus'] ?? ''),
                    'perusahaan'          => $namaInstansi ?: null,
                    'id_instansi'         => $instansi?->ID_instansi ?? null,
                    'awal_masuk'          => $awalMasuk,
                    'tanggal_gajian'      => is_numeric($row['tanggal_gajian'] ?? null) ? (int)$row['tanggal_gajian'] : null,
                    'id_divisi'           => $divisi?->ID_divisi ?? 1,
                    'id_jabatan'          => $jabatan?->ID_jabatan ?? 1,
                    'id_status'           => $status?->ID_status ?? 1,
                ]);

                $this->importedCount++;

            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->importErrors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    private function cleanNumericString($value)
    {
        if ($value === null || $value === '') return null;
        $str = trim((string)$value);
        if (stripos($str, 'e+') !== false || stripos($str, 'e-') !== false) {
            $str = sprintf('%.0f', (float)$str);
        }
        if (str_contains($str, '.')) {
            $parts = explode('.', $str);
            $str = $parts[0];
        }
        return $str;
    }

    private function parseExcelDate($value)
    {
        if (empty($value)) return null;
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
