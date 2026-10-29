<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenGaji extends Model
{
    protected $table = 'komponen_gajis';
    protected $primaryKey = 'ID_komponen_gaji';

    protected $fillable = [
        'nama_gaji',
        'jenis',
        'tipe_hitung',
    ];

    /**
     * Label teks deskriptif untuk basis perhitungan.
     */
    public function getLabelTipeHitungAttribute(): string
    {
        return match ($this->tipe_hitung) {
            'per_jam'           => 'Per Jam Kerja',
            'per_hari'          => 'Per Hari Hadir',
            'per_minggu'        => 'Per Minggu Penuh',
            'per_minggu_rajin'  => 'Per Minggu Rajin',
            'tetap'             => 'Tetap / Bulanan',
            default             => 'Tetap / Bulanan',
        };
    }

    /**
     * Satuan singkat untuk header tabel (/Jam, /Hari, /Mgg, /Bln).
     */
    public function getSatuanAttribute(): string
    {
        return match ($this->tipe_hitung) {
            'per_jam'           => '/Jam',
            'per_hari'          => '/Hari',
            'per_minggu'        => '/Mgg',
            'per_minggu_rajin'  => '/Mgg',
            'tetap'             => '/Bln',
            default             => '/Bln',
        };
    }
}
