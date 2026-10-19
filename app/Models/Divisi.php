<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    protected $table = 'divisis';
    protected $primaryKey = 'ID_divisi';

    protected $fillable = [
        'nama_divisi',
        'tipe_jadwal',
    ];

    /**
     * Cek apakah divisi ini mendukung 2 shift bergilir (Produksi)
     */
    public function isShift(): bool
    {
        return strtolower($this->tipe_jadwal ?? 'staff') === 'shift';
    }

    public function jabatans()
    {
        return $this->hasMany(Jabatan::class, 'ID_divisi', 'ID_divisi');
    }

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'ID_divisi', 'ID_divisi');
    }
}
