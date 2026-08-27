<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class PayrollExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    protected string $bulan;
    protected string $tahun;

    public function __construct(string $bulan, string $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection(): Collection
    {
        return Payroll::with(['karyawan.divisi', 'karyawan.jabatan', 'details'])
            ->whereMonth('date', $this->bulan)
            ->whereYear('date', $this->tahun)
            ->orderBy('date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'ID Karyawan',
            'Nama Karyawan',
            'Divisi',
            'Jabatan',
            'Periode',
            'Hari Masuk',
            'Gaji Pokok (Total)',
            'Total Lemburan',
            'Potongan Kasbon',
            'Potongan BPJS',
            'Potongan Lain-lain',
            'Gaji Bersih (THP)',
        ];
    }

    public function map($payroll): array
    {
        static $no = 0;
        $no++;

        // Aggregate potongan manual (jenis='manual', subtotal < 0) dari detail_payrolls
        $potonganManual = $payroll->details
            ->where('jenis', 'manual')
            ->filter(fn($d) => floatval($d->subtotal) < 0)
            ->sum(fn($d) => abs(floatval($d->subtotal)));

        // Periode dalam format "Bulan Tahun"
        $namaBulan = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];
        $bulanLabel = $namaBulan[str_pad($this->bulan, 2, '0', STR_PAD_LEFT)] ?? $this->bulan;
        $periode    = "{$bulanLabel} {$this->tahun}";

        return [
            $no,
            (string) ($payroll->karyawan->Kode_karyawan ?? ''),
            (string) ($payroll->karyawan->nama_karyawan ?? ''),
            (string) ($payroll->karyawan->divisi->nama_divisi ?? '-'),
            (string) ($payroll->karyawan->jabatan->nama_jabatan ?? '-'),
            $periode,
            (float)   $payroll->total_masuk,
            (float)   $payroll->total_gaji,
            (float)   $payroll->total_lemburan,
            (float)   $payroll->total_kasbon,
            (float)   $payroll->potongan_bpjs,
            (float)   $potonganManual,
            (float)   $payroll->gaji_bersih,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
