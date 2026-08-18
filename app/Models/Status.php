<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';
    protected $primaryKey = 'ID_status';

    protected $fillable = [
        'nama_status',
    ];

    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'ID_status', 'ID_status');
    }
}
