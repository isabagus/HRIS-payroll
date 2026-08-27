<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Kasbon extends Model
{
    protected $table = 'kasbons';
    protected $primaryKey = 'ID_kasbon';

    protected $fillable = [
        'ID_karyawan',
        'total_kasbon',
        'date',
        'deskripsi',
        'status_approval',
        'status_pelunasan',
        'id_approved_by',
        'tanggal_approval',
        'catatan_approval',
    ];

    protected $casts = [
        'date'             => 'date',
        'total_kasbon'     => 'float',
        'tanggal_approval' => 'datetime',
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

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'id_approved_by', 'ID_user');
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

    /**
     * Sinkronkan status_pelunasan dari getSisaKasbon() — lunas jika sisa = 0.
     */
    public function syncStatusPelunasan(?int $upToPayrollId = null): void
    {
        $sisa = $this->getSisaKasbon($upToPayrollId);
        $baru = $sisa <= 0 ? 'lunas' : 'belum_lunas';

        if ($this->status_pelunasan !== $baru) {
            $this->update(['status_pelunasan' => $baru]);
        }
    }
}
