<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $table = 'payrolls';
    protected $primaryKey = 'ID_payroll';

    protected $fillable = [
        'ID_karyawan',
        'ID_gaji',
        'gaji_pokok',
        'date',
        'total_masuk',
        'total_jam_kerja',
        'total_izin',
        'total_alpha',
        'total_gaji',
        'total_lemburan',
        'total_kasbon',
        'potongan_lain',
        'gaji_bersih',
        'status_payroll',
    ];

    protected $casts = [
        'date'            => 'date',
        'gaji_pokok'      => 'float',
        'total_masuk'     => 'float',
        'total_jam_kerja' => 'float',
        'total_gaji'      => 'float',
        'total_lemburan'  => 'float',
        'total_kasbon'    => 'float',
        'potongan_lain'   => 'float',
        'gaji_bersih'     => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'ID_karyawan', 'ID_karyawan')->withTrashed();
    }

    public function details()
    {
        return $this->hasMany(DetailPayroll::class, 'ID_payroll', 'ID_payroll');
    }

    // Dynamic accessor summing BPJS deduction from detail_payrolls
    public function getPotonganBpjsAttribute()
    {
        $val = $this->details->where('jenis', 'bpjs')->where('subtotal', '<', 0)->sum('subtotal');
        return abs($val);
    }

    // Dynamic accessor reading BPJS company burden from detail_payrolls info
    public function getBpjsPerusahaanAttribute()
    {
        if (isset($this->attributes['bpjs_perusahaan'])) {
            return floatval($this->attributes['bpjs_perusahaan']);
        }
        $detail = $this->details->where('jenis', 'bpjs')->first(function ($d) {
            return str_contains(strtolower($d->keterangan ?? ''), 'perusahaan');
        });
        if ($detail) {
            preg_match('/Rp\s*([\d\.,]+)/i', $detail->keterangan, $matches);
            if (!empty($matches[1])) {
                $clean = str_replace(['.', ','], '', $matches[1]);
                return floatval($clean);
            }
        }
        return 0;
    }

    /** Tarif gaji pokok per jam (kolom gaji_pokok di DB). */
    public function getTarifPerJamAttribute(): float
    {
        return floatval($this->attributes['gaji_pokok'] ?? 0);
    }

    /** Total jam kerja efektif — gunakan nilai DB, fallback dari total_gaji ÷ tarif. */
    public function getTotalJamKerjaEfektifAttribute(): float
    {
        $jam = floatval($this->attributes['total_jam_kerja'] ?? 0);
        if ($jam > 0) {
            return $jam;
        }

        $tarif = floatval($this->attributes['gaji_pokok'] ?? 0);
        $totalGaji = floatval($this->attributes['total_gaji'] ?? 0);
        if ($tarif > 0 && $totalGaji > 0) {
            return round($totalGaji / $tarif, 2);
        }

        return floatval($this->attributes['total_masuk'] ?? 0) * 8.0;
    }

    /** Gaji pokok kalkulasi = total_jam_kerja × tarif_per_jam. */
    public function getGajiPokokKalkulasiAttribute(): float
    {
        $totalGaji = floatval($this->attributes['total_gaji'] ?? 0);
        if ($totalGaji > 0) {
            return $totalGaji;
        }

        return $this->total_jam_kerja_efektif * $this->tarif_per_jam;
    }

    public function getTotalKasbonAttribute()
    {
        return $this->attributes['total_kasbon'] ?? 0;
    }

    public function getGajiBersihAttribute()
    {
        if (isset($this->attributes['gaji_bersih'])) {
            return $this->attributes['gaji_bersih'];
        }
        $gp = $this->gaji_pokok_kalkulasi;
        $detailsSubtotal = $this->details->sum('subtotal');
        return max(0, $gp + $detailsSubtotal);
    }

    public function getStatusPayrollAttribute()
    {
        return $this->attributes['status_payroll'] ?? 'Dibayar';
    }
}
