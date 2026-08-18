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

    // Accessors for column backward compatibility
    public function getGajiPokokAttribute()
    {
        return $this->attributes['gaji_pokok'] ?? $this->attributes['total_gaji'] ?? 0;
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
        $gp = $this->gaji_pokok;
        $detailsSubtotal = $this->details->sum('subtotal');
        return max(0, $gp + $detailsSubtotal);
    }

    public function getStatusPayrollAttribute()
    {
        return $this->attributes['status_payroll'] ?? 'Dibayar';
    }
}
