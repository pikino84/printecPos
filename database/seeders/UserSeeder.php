<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Partner;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Obtener el partner de Printec por slug (fallback al env si no existe)
        $printecId = Partner::where('slug', 'printec')->value('id') ?? (int) env('PRINTEC_PARTNER_ID', 1);

        // 2) Asegurar roles (por si el orden de seeders cambia)
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $adminRole      = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);

        // 3) Usuarios a crear (en desarrollo puedes sacar las contraseñas del .env)
        $users = [
            [
                'name'  => 'Francisco Ayapantecalth',
                'email' => 'jfcruz@outlook.com',
                'password'   => 'P4$$wOrd-2025SA',
                'partner_id' => $printecId,
                'roles'      => ['super admin'],
            ],
            [
                'name'  => 'Eduardo Butrón',
                'email' => 'ebutron@printec.mx',
                'password'   => 'L4l0#$@@$#',
                'partner_id' => $printecId,
                'roles'      => ['super admin'],
            ],
            [
                'name'  => 'Ingrid Martinez',
                'email' => 'ingrid@printec.mx',
                'password'   => 'L4l0#$@@$#',
                'partner_id' => $printecId,
                'roles'      => ['super admin'],
            ],
            [
                'name'  => 'Francisco 2',
                'email' => 'pikino84@gmail.com',
                'password'   => 'P4$$wOrd-2025SA',
                'partner_id' => $printecId,
                'roles'      => ['admin'],
            ],
        ];

        // 4) Crear/actualizar y asignar roles
        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'              => $u['name'],
                    'password'          => Hash::make($u['password']),
                    'partner_id'        => $u['partner_id'],
                    'email_verified_at' => now(),
                ]
            );

            // Asigna exactamente los roles indicados (idempotente)
            $user->syncRoles($u['roles']);
        }

        // 5) (Opcional) Mensaje de consola
        $this->command?->info('Users sembrados y roles asignados correctamente.');
    }
}
