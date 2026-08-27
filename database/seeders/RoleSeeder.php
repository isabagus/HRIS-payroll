<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['nama_role' => 'Super Admin']);
        $managerRole    = Role::firstOrCreate(['nama_role' => 'Manager']);
        $adminRole      = Role::firstOrCreate(['nama_role' => 'Admin']);

        // Create Default Super Admin User if not existing
        if (!User::where('username', 'root')->exists()) {
            User::create([
                'username' => 'root',
                'password' => Hash::make('root123'),
                'ID_role'  => $superAdminRole->ID_role,
            ]);
        }

        // Create Default Manager User if not existing
        if (!User::where('username', 'manager')->exists()) {
            User::create([
                'username' => 'manager',
                'password' => Hash::make('manager123'),
                'ID_role'  => $managerRole->ID_role,
            ]);
        }

        // Create Default Admin User if not existing
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'ID_role'  => $adminRole->ID_role,
            ]);
        }
    }
}
