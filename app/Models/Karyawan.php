<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Karyawan extends Model
{
    use SoftDeletes;

    protected $table = 'karyawans';
    protected $primaryKey = 'ID_karyawan';

    protected $fillable = [
        'Kode_karyawan',
        'nama_karyawan',
        'email',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_tlp',
        'pendidikan_terakhir',
        'prodi',
        'tahun_lulus',
        'perusahaan',
        'awal_masuk',
        'tanggal_gajian',
        'id_divisi',
        'id_jabatan',
        'id_status',
        'id_instansi',
        'tanggal_resign',
        'alasan_resign',
    ];

    protected $casts = [
        'tanggal_lahir'  => 'date',
        'awal_masuk'     => 'date',
        'tanggal_resign' => 'date',
    ];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi', 'ID_divisi');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'ID_jabatan');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'id_status', 'ID_status');
    }

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'id_instansi', 'ID_instansi');
    }

    // Accessor for age (Umur)
    public function getUmurAttribute()
    {
        if (!$this->tanggal_lahir) return '-';
        return Carbon::parse($this->tanggal_lahir)->age . ' Thn';
    }

    // Accessor for working duration (Masa Kerja)
    public function getMasaKerjaAttribute()
    {
        if (!$this->awal_masuk) return '-';
        $endDate = $this->tanggal_resign ?? Carbon::now();
        $totalMonths = (int) Carbon::parse($this->awal_masuk)->diffInMonths($endDate);
        if ($totalMonths >= 12) {
            $years = floor($totalMonths / 12);
            $months = $totalMonths % 12;
            return "{$years} TAHUN {$months} BULAN";
        }
        return "{$totalMonths} BULAN";
    }

    public function getRouteKeyName()
    {
        return 'ID_karyawan';
    }
}
