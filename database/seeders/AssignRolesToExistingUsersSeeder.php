<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AssignRolesToExistingUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Mapea tus correos EXISTENTES a roles (ajusta a tu gusto)
        $map = [
            'jfcruz@outlook.com' => 'super admin',
            'ebutron@printec.mx' => 'super admin',
            'ingrid@printec.mx'  => 'super admin',
            'pikino84@gmail.com' => 'admin',
            // agrega más si quieres...
        ];

        foreach ($map as $email => $role) {
            $u = User::where('email', $email)->first();
            if ($u && !$u->hasRole($role)) {
                $u->assignRole($role);
            }
        }
    }
}
