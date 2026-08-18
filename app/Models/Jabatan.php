<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatans';
    protected $primaryKey = 'ID_jabatan';

    protected $fillable = [
        'nama_jabatan',
    ];

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'ID_jabatan', 'ID_jabatan');
    }
}
