<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'ID_user';

    protected $fillable = [
        'username',
        'email',
        'password',
        'ID_karyawan',
        'ID_role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'ID_role', 'ID_role');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'ID_karyawan', 'ID_karyawan');
    }

    /**
     * Check if user's role has permission for a specific module and action.
     * 1. Super Admin always returns true (full bypass).
     * 2. Otherwise checks hak_akses_role for user's ID_role + modul.
     */
    public function hasHakAkses(string $modul, string $action): bool
    {
        if ($this->role && strtolower($this->role->nama_role) === 'super admin') {
            return true;
        }

        if (!$this->ID_role) {
            return false;
        }

        $permission = HakAksesRole::where('id_role', $this->ID_role)
            ->where('modul', $modul)
            ->first();

        if (!$permission) {
            return false;
        }

        $column = 'can_' . strtolower($action);
        return (bool) ($permission->$column ?? false);
    }
}
