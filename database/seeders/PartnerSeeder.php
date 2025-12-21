<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partner;
use App\Models\PartnerEntity;
use App\Models\PartnerEntityBankAccount;
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
                'contact_email'    => 'eduardo@printec.mx',
                'direccion'        => 'Calle Ficticia 123, CDMX',
                'type'             => 'mixto', // si tu tabla tiene este campo
            ]
        );

        // Crear entidad principal de Printec con condiciones de pago
        $printecEntity = PartnerEntity::updateOrCreate(
            ['partner_id' => $printec->id, 'razon_social' => 'Eduardo Butrón Sáyago'],
            [
                'rfc' => 'BUSE841225G53',
                'telefono' => '9981669212',
                'correo_contacto' => 'eduardo@printec.mx',
                'is_default' => true,
                'is_active' => true,
                'payment_terms' => "* Ésta cotización tiene una vigencia de 8 días.\n* El precio reflejado no incluye envío\n* Tiempo de entrega 15 días hábiles a partir de la recepción del pago.\n* Si requiere entrega a un plazo menor aplicará un cargo adicional del 30%",
            ]
        );

        // Asignar entidad por defecto al partner
        $printec->update(['default_entity_id' => $printecEntity->id]);

        // Crear cuenta bancaria de Printec
        PartnerEntityBankAccount::updateOrCreate(
            ['partner_entity_id' => $printecEntity->id, 'bank_name' => 'XXX'],
            [
                'account_holder' => 'XXX',
                'account_number' => 'XXX',
                'clabe' => 'XXX',
                'currency' => 'MXN',
                'is_default' => true,
                'is_active' => true,
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
            ]
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
