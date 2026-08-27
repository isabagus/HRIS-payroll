<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\HakAksesRole;

class HakAksesRoleController extends Controller
{
    private array $modules = [
        'instansi'             => 'Master Data Instansi',
        'divisi'               => 'Master Data Divisi',
        'jabatan'              => 'Master Data Jabatan',
        'status_karyawan'      => 'Master Data Status Karyawan',
        'gaji_pokok'           => 'Master Data Komponen Gaji',
        'karyawan'             => 'Data Karyawan',
        'gaji_karyawan'        => 'Pengaturan Gaji Karyawan',
        'absensi'              => 'Rekap & Input Absensi',
        'lembur'               => 'Lembur Karyawan',
        'kasbon'               => 'Kasbon Karyawan',
        'payroll'              => 'Payroll & Penggajian',
        'pengaturan_jam_kerja' => 'Pengaturan Jam Kerja & Toleransi',
        'reset_password'       => 'Permintaan Reset Password',
        'user_management'      => 'User Management & Hak Akses',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || strtolower($user->role?->nama_role ?? '') !== 'super admin') {
            return redirect()->route('dashboard')->with('error', 'Halaman Kelola Hak Akses hanya dapat diakses oleh Super Admin.');
        }

        $roles = Role::where('nama_role', '!=', 'Super Admin')->get();
        $selectedRoleId = (int) $request->get('role_id', $roles->first()?->ID_role);

        $selectedRole = Role::find($selectedRoleId);
        if (!$selectedRole && $roles->count() > 0) {
            $selectedRole = $roles->first();
            $selectedRoleId = $selectedRole->ID_role;
        }

        $permissionsRaw = HakAksesRole::where('id_role', $selectedRoleId)->get();
        $permissions = [];
        foreach ($permissionsRaw as $p) {
            $permissions[$p->modul] = [
                'can_create' => (bool)$p->can_create,
                'can_read'   => (bool)$p->can_read,
                'can_update' => (bool)$p->can_update,
                'can_delete' => (bool)$p->can_delete,
            ];
        }

        $modules = $this->modules;

        return view('layouts.hak_akses.index', compact('roles', 'selectedRole', 'selectedRoleId', 'modules', 'permissions'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if (!$user || strtolower($user->role?->nama_role ?? '') !== 'super admin') {
            return redirect()->route('dashboard')->with('error', 'Halaman Kelola Hak Akses hanya dapat diakses oleh Super Admin.');
        }

        $roleId = $request->input('role_id');
        $role = Role::find($roleId);

        if (!$role || strtolower($role->nama_role) === 'super admin') {
            return redirect()->back()->with('error', 'Role tidak valid.');
        }

        $submittedPermissions = $request->input('permissions', []);

        foreach ($this->modules as $modulKey => $modulName) {
            $modulPerms = $submittedPermissions[$modulKey] ?? [];

            $canCreate = !empty($modulPerms['can_create']);
            $canRead   = !empty($modulPerms['can_read']);
            $canUpdate = !empty($modulPerms['can_update']);
            $canDelete = !empty($modulPerms['can_delete']);

            // If all are false, delete row or keep as false
            HakAksesRole::updateOrCreate(
                [
                    'id_role' => $role->ID_role,
                    'modul'   => $modulKey,
                ],
                [
                    'can_create' => $canCreate,
                    'can_read'   => $canRead,
                    'can_update' => $canUpdate,
                    'can_delete' => $canDelete,
                ]
            );
        }

        return redirect()->route('hak-akses.index', ['role_id' => $role->ID_role])
            ->with('success', "Hak akses role '{$role->nama_role}' berhasil diperbarui!");
    }
}
