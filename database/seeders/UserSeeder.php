<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crear usuario Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'jcruz@outlook.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('P4$$wOrd-2025SA'),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // Crear usuario Admin
        $admin = User::firstOrCreate(
            ['email' => 'pikino84@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('P4$$wOrd-2025A'),
            ]
        );
        $admin->assignRole($adminRole);
    }
}