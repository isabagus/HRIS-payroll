<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    protected $table = 'divisis';
    protected $primaryKey = 'ID_divisi';

    protected $fillable = [
        'nama_divisi',
    ];

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'ID_divisi', 'ID_divisi');
    }
}
