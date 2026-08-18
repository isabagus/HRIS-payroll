<?php

namespace App\Exports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class KaryawanExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder
{
    public function collection()
    {
        return Karyawan::with(['divisi', 'jabatan', 'status', 'instansi'])->get();
    }

    public function headings(): array
    {
        return [
            'ID Karyawan',
            'NAMA',
            'EMAIL',
            'NO TELEPON',
            'DEVISI',
            'JABATAN',
            'STATUS',
            'INSTANSI',
            'NIK',
            'Tempat Lahir',
            'TTL',
            'JENIS KELAMIN',
            'ALAMAT',
            'PENDIDIKAN TERAKHIR',
            'PRODI',
            'TAHUN LULUS',
            'AWAL MASUK',
            'TANGGAL GAJIAN',
        ];
    }

    public function map($karyawan): array
    {
        // Clean NIK if stored as scientific notation in DB
        $nik = trim((string)($karyawan->nik ?? ''));
        if (!empty($nik) && (stripos($nik, 'e+') !== false || stripos($nik, 'e-') !== false)) {
            $nik = sprintf('%.0f', (float)$nik);
        }

        // Clean Phone Number
        $noTlp = trim((string)($karyawan->no_tlp ?? ''));
        if (!empty($noTlp) && (stripos($noTlp, 'e+') !== false || stripos($noTlp, 'e-') !== false)) {
            $noTlp = sprintf('%.0f', (float)$noTlp);
        }

        return [
            (string) ($karyawan->Kode_karyawan ?? ''),
            (string) ($karyawan->nama_karyawan ?? ''),
            (string) ($karyawan->email ?? ''),
            $noTlp,
            (string) ($karyawan->divisi?->nama_divisi ?? '-'),
            (string) ($karyawan->jabatan?->nama_jabatan ?? '-'),
            (string) ($karyawan->status?->nama_status ?? 'Karyawan Tetap'),
            (string) ($karyawan->instansi?->nama_instansi ?? $karyawan->perusahaan ?? '-'),
            $nik,
            (string) ($karyawan->tempat_lahir ?? ''),
            $karyawan->tanggal_lahir ? $karyawan->tanggal_lahir->format('Y-m-d') : '',
            $karyawan->jenis_kelamin === 'L' ? 'LAKI-LAKI' : 'PEREMPUAN',
            (string) ($karyawan->alamat ?? ''),
            (string) ($karyawan->pendidikan_terakhir ?? ''),
            (string) ($karyawan->prodi ?? ''),
            (string) ($karyawan->tahun_lulus ?? ''),
            $karyawan->awal_masuk ? $karyawan->awal_masuk->format('Y-m-d') : '',
            $karyawan->tanggal_gajian ? (string)$karyawan->tanggal_gajian : '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        $col = $cell->getColumn();
        
        // Force explicit string data type for NIK (I), NO TELEPON (D), ID Karyawan (A) or any long number
        if (in_array($col, ['A', 'D', 'I']) || (is_string($value) && is_numeric($value) && strlen($value) >= 10)) {
            $cell->setValueExplicit((string)$value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
