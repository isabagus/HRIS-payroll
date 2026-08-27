<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanResetPassword extends Model
{
    protected $table = 'permintaan_reset_passwords';

    protected $fillable = [
        'id_user',
        'password_baru_hash',
        'password_lama_hash',
        'kode_karyawan_input',
        'status',
        'id_diproses_oleh',
        'tanggal_diproses',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_diproses' => 'datetime',
    ];

    /**
     * User yang mengajukan permintaan reset password.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'ID_user');
    }

    /**
     * Super Admin yang memproses permintaan ini.
     */
    public function diprosesoleh()
    {
        return $this->belongsTo(User::class, 'id_diproses_oleh', 'ID_user');
    }
}
