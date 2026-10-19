<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatans';
    protected $primaryKey = 'ID_jabatan';

    protected $fillable = [
        'nama_jabatan',
        'ID_divisi',
        'tipe_jadwal',
    ];

    /**
     * Relasi ke Divisi
     */
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'ID_divisi', 'ID_divisi');
    }

    /**
     * Cek apakah jabatan ini mengikuti multi-shift bergilir
     */
    public function isShift(): bool
    {
        return strtolower($this->tipe_jadwal ?? 'staff') === 'shift';
    }

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'ID_jabatan', 'ID_jabatan');
    }
}

