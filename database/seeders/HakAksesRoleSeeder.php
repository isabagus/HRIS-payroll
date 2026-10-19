<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\HakAksesRole;

class HakAksesRoleSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::where('nama_role', 'Super Admin')->first();
        $manager    = Role::where('nama_role', 'Manager')->first();
        $admin      = Role::where('nama_role', 'Admin')->first();

        $allModules = [
            'instansi',
            'divisi',
            'jabatan',
            'status_karyawan',
            'gaji_pokok',
            'shift',
            'karyawan',
            'gaji_karyawan',
            'absensi',
            'lembur',
            'kasbon',
            'payroll',
            'pengaturan_jam_kerja',
            'reset_password',
            'user_management',
        ];

        // 1. Super Admin: NO rows in hak_akses_role (always bypasses all checks)
        if ($superAdmin) {
            HakAksesRole::where('id_role', $superAdmin->ID_role)->delete();
        }

        // 2. Manager: ALL modules EXCEPT 'user_management' with full true permissions
        if ($manager) {
            $managerModules = array_diff($allModules, ['user_management']);
            foreach ($managerModules as $modul) {
                HakAksesRole::updateOrCreate(
                    ['id_role' => $manager->ID_role, 'modul' => $modul],
                    [
                        'can_create' => true,
                        'can_read'   => true,
                        'can_update' => true,
                        'can_delete' => true,
                    ]
                );
            }
            // Ensure user_management is not present for Manager
            HakAksesRole::where('id_role', $manager->ID_role)->where('modul', 'user_management')->delete();
        }

        // 3. Admin: ONLY 'karyawan', 'gaji_karyawan', 'absensi', 'lembur', 'kasbon', 'payroll' with full true permissions
        if ($admin) {
            $adminModules = [
                'karyawan',
                'gaji_karyawan',
                'absensi',
                'lembur',
                'kasbon',
                'payroll',
            ];
            foreach ($adminModules as $modul) {
                HakAksesRole::updateOrCreate(
                    ['id_role' => $admin->ID_role, 'modul' => $modul],
                    [
                        'can_create' => true,
                        'can_read'   => true,
                        'can_update' => true,
                        'can_delete' => true,
                    ]
                );
            }
            // Remove any other modules for Admin
            HakAksesRole::where('id_role', $admin->ID_role)
                ->whereNotIn('modul', $adminModules)
                ->delete();
        }
    }
}
