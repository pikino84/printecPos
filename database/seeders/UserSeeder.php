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
        // Crear usuario Francisco
        $superAdmin = User::firstOrCreate(
            ['email' => 'jfcruz@outlook.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('P4$$wOrd-2025SA'),
            ]
        );
        $superAdminRole = Role::where('name', 'super admin')->first();
        if ($superAdminRole) {
            $superAdmin->assignRole($superAdminRole);
        }

        // Crear super admin ebutron@printec.mx
        $superAdmin2 = User::firstOrCreate(
            ['email' => 'ebutron@printec.mx"'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('L4l0#$@@$#'),
            ]
        );
        $superAdminRole2 = Role::where('name', 'super admin')->first();
        if ($superAdminRole2) {
            $superAdmin2->assignRole($superAdminRole2);
        }
        // Crear usuario super admin ingrid@printec.mx
        $superAdmin3 = User::firstOrCreate(
            ['email' => 'ingrid@printec.mx'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('L4l0#$@@$#'),
            ]
        );
        $superAdminRole3 = Role::where('name', 'super admin')->first();
        if ($superAdminRole3) {
            $superAdmin3->assignRole($superAdminRole3);
        }
        // Crear usuario admin
        $admin = User::firstOrCreate(
            ['email' => 'pikino84@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('P4$$wOrd-2025A'),
            ]
        );
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }
    }
}