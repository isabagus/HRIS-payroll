<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GajiKaryawan extends Model
{
    protected $table = 'gaji_karyawans';
    protected $primaryKey = 'ID_gaji';

    protected $fillable = [
        'ID_karyawan',
        'date',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'ID_karyawan', 'ID_karyawan')->withTrashed();
    }

    public function details()
    {
        return $this->hasMany(DetailGajiKaryawan::class, 'ID_gaji', 'ID_gaji');
    }
}
