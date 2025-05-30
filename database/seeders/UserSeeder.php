<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Asociado;
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
                'name' => 'Francisco Ayapantecalth',
                'password' => Hash::make('P4$$wOrd-2025SA'),
            ]
        );
        $superAdminRole = Role::where('name', 'super admin')->first();
        if ($superAdminRole) {
            $superAdmin->assignRole($superAdminRole);
        }

        // Crear super admin ebutron@printec.mx
        $superAdmin2 = User::firstOrCreate(
            ['email' => 'ebutron@printec.mx'],
            [
                'name' => 'Eduardo Butrón',
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
                'name' => 'Ingrid Martinez',
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
        /*
        // Buscar asociados creados en el seeder
        $asociado1 = Asociado::where('nombre_comercial', 'Distribuidora Alpha')->first();
        $asociado2 = Asociado::where('nombre_comercial', 'Beta Industrial')->first();

        // Crear usuarios para asociado 1
        User::create([
            'name' => 'Vendedor Alpha 1',
            'email' => 'vendedor1@alpha.com',
            'password' => Hash::make('password'),
            'asociado_id' => $asociado1->id,
        ]);

        User::create([
            'name' => 'Vendedor Alpha 2',
            'email' => 'vendedor2@alpha.com',
            'password' => Hash::make('password'),
            'asociado_id' => $asociado1->id,
        ]);

        // Crear usuarios para asociado 2
        User::create([
            'name' => 'Vendedor Beta',
            'email' => 'vendedor@beta.com',
            'password' => Hash::make('password'),
            'asociado_id' => $asociado2->id,
        ]);
        */
    }
}