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
        // Crear usuario Super Admin
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

        // Crear usuario Admin
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