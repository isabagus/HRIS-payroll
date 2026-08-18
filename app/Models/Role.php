<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'ID_role';
    protected $fillable = ['nama_role'];

    public function hakAkses()
    {
        return $this->hasMany(HakAksesRole::class, 'id_role', 'ID_role');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'ID_role', 'ID_role');
    }
}
