<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriLembur extends Model
{
    protected $table = 'kategori_lemburs';
    protected $primaryKey = 'ID_kategori_lembur';

    protected $fillable = [
        'tipe_lembur',
    ];

    public function lemburs()
    {
        return $this->hasMany(Lembur::class, 'ID_kategori_lembur', 'ID_kategori_lembur');
    }
}
