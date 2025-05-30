<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asociado;
use Illuminate\Support\Facades\DB;

class AsociadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // 👈 Desactiva claves foráneas
        Asociado::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); 

        Asociado::create([
            'nombre_comercial' => 'Distribuidora Alpha',
            'razon_social' => 'Distribuidora Alpha S.A. de C.V.',
            'rfc' => 'ALP123456789',
            'telefono' => '555-123-4567',
            'correo_contacto' => 'contacto@alpha.com',
            'direccion' => 'Calle Ficticia 123, CDMX',
        ]);

        Asociado::create([
            'nombre_comercial' => 'Beta Industrial',
            'razon_social' => 'Beta Industrial S.A. de C.V.',
            'rfc' => 'BET987654321',
            'telefono' => '555-987-6543',
            'correo_contacto' => 'ventas@beta.com',
            'direccion' => 'Avenida Imaginaria 456, Guadalajara',
        ]);
    }
}
