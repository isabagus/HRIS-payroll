<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensis';
    protected $primaryKey = 'ID_absensi';

    protected $fillable = [
        'ID_karyawan',
        'date',
        'jam_kerja_masuk',
        'jam_kerja_pulang',
        'durasi_kerja',
        'jam_masuk',
        'jam_pulang',
        'terlambat',
        'pulang_cepat',
        'total_jam_kerja',
        'istirahat',
        'lembur_awal',
        'lembur_akhir',
        'total_lembur',
        'masuk_kerja',
        'status_absensi',
        'keterangan',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'ID_karyawan', 'ID_karyawan');
    }
}
