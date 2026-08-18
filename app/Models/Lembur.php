<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    protected $table = 'lemburs';
    protected $primaryKey = 'ID_lembur';

    protected $fillable = [
        'ID_karyawan',
        'ID_kategori_lembur',
        'date',
        'jam_mulai',
        'jam_selesai',
        'durasi_lembur',
        'tarif_per_jam',
        'uang_lembur',
        'uang_makan',
        'deskripsi',
        'status_persetujuan',
        'id_approved_by',
        'tanggal_approval',
        'catatan_approval',
    ];

    protected $casts = [
        'date'             => 'date',
        'durasi_lembur'    => 'float',
        'tarif_per_jam'    => 'float',
        'uang_lembur'      => 'float',
        'uang_makan'       => 'float',
        'tanggal_approval' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'ID_karyawan', 'ID_karyawan')->withTrashed();
    }

    public function kategoriLembur()
    {
        return $this->belongsTo(KategoriLembur::class, 'ID_kategori_lembur', 'ID_kategori_lembur');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'id_approved_by', 'ID_user');
    }

    // Dynamic accessor mapping tipe_lembur from kategori_lemburs table
    public function getJenisLemburAttribute()
    {
        $nama = strtolower($this->kategoriLembur?->tipe_lembur ?? '');
        return str_contains($nama, 'libur') ? 'libur' : 'harian';
    }

    // Accessors for blade view compatibility
    public function getTanggalAttribute()
    {
        return $this->date;
    }

    public function getDurasiJamAttribute()
    {
        return $this->durasi_lembur ?? 0;
    }

    public function getTotalUangLemburAttribute()
    {
        return $this->uang_lembur ?? 0;
    }

    public function getKeteranganAttribute()
    {
        return $this->deskripsi ?? '-';
    }

    public function detailPayrolls()
    {
        return $this->hasMany(DetailPayroll::class, 'ID_lembur', 'ID_lembur');
    }
}
