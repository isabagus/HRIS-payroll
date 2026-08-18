<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HakAksesRole extends Model
{
    protected $table = 'hak_akses_role';
    
    protected $fillable = [
        'id_role',
        'modul',
        'can_create',
        'can_read',
        'can_update',
        'can_delete',
    ];

    protected $casts = [
        'can_create' => 'boolean',
        'can_read'   => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'ID_role');
    }
}
