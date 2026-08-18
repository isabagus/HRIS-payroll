<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailGajiKaryawan extends Model
{
    protected $table = 'detail_gaji_karyawans';

    protected $fillable = [
        'ID_gaji',
        'ID_komponen_gaji',
        'nominal_gaji',
    ];

    protected $casts = [
        'nominal_gaji' => 'float',
    ];

    public function gajiKaryawan()
    {
        return $this->belongsTo(GajiKaryawan::class, 'ID_gaji', 'ID_gaji');
    }

    public function komponenGaji()
    {
        return $this->belongsTo(KomponenGaji::class, 'ID_komponen_gaji', 'ID_komponen_gaji');
    }
}
