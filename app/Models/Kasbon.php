<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kasbon extends Model
{
    protected $table = 'kasbons';
    protected $primaryKey = 'ID_kasbon';

    protected $fillable = [
        'ID_karyawan',
        'total_kasbon',
        'date',
        'deskripsi',
        'status',
    ];

    protected $casts = [
        'date'         => 'date',
        'total_kasbon' => 'float',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'ID_karyawan', 'ID_karyawan')->withTrashed();
    }

    // Accessors for blade compatibility
    public function getTanggalAttribute()
    {
        return $this->date;
    }

    public function getKeteranganAttribute()
    {
        return $this->deskripsi ?? '-';
    }

    public function detailPayrolls()
    {
        return $this->hasMany(DetailPayroll::class, 'ID_kasbon', 'ID_kasbon');
    }

    /**
     * Hitung sisa kasbon yang belum dibayar.
     * jika $upToPayrollId diberikan, hitung sisa SETELAH pemotongan pada payroll tersebut.
     */
    public function getSisaKasbon(?int $upToPayrollId = null): float
    {
        $query = $this->detailPayrolls()->where('jenis', 'kasbon');

        if ($upToPayrollId !== null) {
            $query->where('ID_payroll', '<=', $upToPayrollId);
        }

        $totalDibayar = abs((float) $query->sum('subtotal'));
        $sisa = (float) $this->total_kasbon - $totalDibayar;

        return max(0, $sisa);
    }

    /**
     * Hitung akumulasi cicilan yang sudah dipotong.
     */
    public function getTotalSudahDibayar(?int $upToPayrollId = null): float
    {
        $query = $this->detailPayrolls()->where('jenis', 'kasbon');

        if ($upToPayrollId !== null) {
            $query->where('ID_payroll', '<=', $upToPayrollId);
        }

        return abs((float) $query->sum('subtotal'));
    }

    public function getSisaKasbonAttribute(): float
    {
        return $this->getSisaKasbon();
    }

    public function getTotalSudahDibayarAttribute(): float
    {
        return $this->getTotalSudahDibayar();
    }
}
