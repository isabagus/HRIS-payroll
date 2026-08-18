<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanTarif extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_tarif';

    protected $fillable = [
        'nama_parameter',
        'nilai',
        'keterangan',
    ];
}
