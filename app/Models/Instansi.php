<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $table = 'instansis';
    protected $primaryKey = 'ID_instansi';

    protected $fillable = [
        'nama_instansi',
        'logo',
    ];

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'id_instansi', 'ID_instansi');
    }
}
