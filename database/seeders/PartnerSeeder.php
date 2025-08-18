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
                'name' => 'Printec',
                'contact_name'     => 'Eduardo Butron',
                'contact_phone'    => '9981669212',
                'contact_email'    => 'ebutron@printec.mx',
                'direccion'        => 'Calle Ficticia 123, CDMX',
                'type'             => 'mixto', // si tu tabla tiene este campo
            ]
        );

        // 2) Proveedores y otros partners
        $partners = [
            [
                'slug' => 'doble-vela',
                'name' => 'Doble Vela',
                'contact_name' => 'Juan Perez',
                'contact_phone' => '555-123-4567',
                'contact_email' => 'juan@doblevela.com',
                'direccion' => 'Calle Ficticia 456, CDMX',
                'type' => 'proveedor',
            ],
            [
                'slug' => '4promotional',
                'name' => '4Promotional',
                'contact_name' => 'Maria Lopez',
                'contact_phone' => '555-987-6543',
                'contact_email' => null,
                'direccion' => null,
                'type' => 'proveedor',
            ],
            [
                'slug' => 'beta-industrial',
                'name' => 'Beta Industrial',
                'contact_name' => 'Beta Industrial S.A. de C.V.',
                'contact_phone' => '555-987-6543',
                'contact_email' => 'ventas@beta.com',
                'direccion' => 'Avenida Imaginaria 456, Guadalajara',
                'type' => 'asociado',
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
