<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permManageUsers = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        $permEditProfile = Permission::firstOrCreate(['name' => 'edit profile', 'guard_name' => 'web']);

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Asignar permisos a roles
        $adminRole->syncPermissions([$permManageUsers]);
        $userRole->syncPermissions([$permEditProfile]);

        // Asignar rol al usuario 1
        $user = User::find(1);
        if ($user) {
            $user->assignRole($adminRole);
        }
    }
}
