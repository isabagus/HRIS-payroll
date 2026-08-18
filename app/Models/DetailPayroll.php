<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPayroll extends Model
{
    protected $table = 'detail_payrolls';

    protected $fillable = [
        'ID_payroll',
        'ID_lembur',
        'ID_kasbon',
        'jenis',
        'subtotal',
        'keterangan',
    ];

    protected $casts = [
        'subtotal' => 'float',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'ID_payroll', 'ID_payroll');
    }

    public function lembur()
    {
        return $this->belongsTo(Lembur::class, 'ID_lembur', 'ID_lembur');
    }

    public function kasbon()
    {
        return $this->belongsTo(Kasbon::class, 'ID_kasbon', 'ID_kasbon');
    }
}
