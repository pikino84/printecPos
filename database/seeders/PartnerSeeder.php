<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        // En desarrollo solemos reiniciar; si lo usas en prod, quita estas 3 líneas.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Partner::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1) PRINTEC primero => garantizar id=1 en migrate:fresh
        $printec = Partner::updateOrCreate(
            ['slug' => 'printec'],
            [
                'nombre_comercial' => 'Printec',
                'razon_social'     => 'Distribuidora Alpha S.A. de C.V.',
                'rfc'              => 'ALP123456789',
                'telefono'         => '555-123-4567',
                'correo_contacto'  => 'contacto@alpha.com',
                'direccion'        => 'Calle Ficticia 123, CDMX',
                'tipo'             => 'mixto', // si tu tabla tiene este campo
            ]
        );

        // 2) Proveedores y otros partners
        $partners = [
            [
                'slug' => 'doble-vela',
                'nombre_comercial' => 'Doble Vela',
                'razon_social' => 'Doble Vela S.A.',
                'rfc' => null,
                'telefono' => null,
                'correo_contacto' => null,
                'direccion' => null,
                'tipo' => 'proveedor',
            ],
            [
                'slug' => '4promotional',
                'nombre_comercial' => '4Promotional',
                'razon_social' => '4Promotional S.A.',
                'rfc' => null,
                'telefono' => null,
                'correo_contacto' => null,
                'direccion' => null,
                'tipo' => 'proveedor',
            ],
            [
                'slug' => 'beta-industrial',
                'nombre_comercial' => 'Beta Industrial',
                'razon_social' => 'Beta Industrial S.A. de C.V.',
                'rfc' => 'BET987654321',
                'telefono' => '555-987-6543',
                'correo_contacto' => 'ventas@beta.com',
                'direccion' => 'Avenida Imaginaria 456, Guadalajara',
                'tipo' => 'asociado',
            ],
        ];

        foreach ($partners as $p) {
            Partner::updateOrCreate(
                ['slug' => $p['slug']],
                $p
            );
        }

        // (Opcional) mensaje de verificación en consola
        $this->command?->info('Partners sembrados. PRINTEC_ID='.$printec->id.' (esperado 1).');
    }
}
