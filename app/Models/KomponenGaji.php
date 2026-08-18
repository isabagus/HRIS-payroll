<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenGaji extends Model
{
    protected $table = 'komponen_gajis';
    protected $primaryKey = 'ID_komponen_gaji';

    protected $fillable = [
        'nama_gaji',
    ];
}
