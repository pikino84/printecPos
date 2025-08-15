<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partner;

class ProductPartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            [
                'nombre_comercial' => 'Innovation',
                'slug' => 'innovation',
                'razon_social' => 'Innovation S.A.',
                'tipo' => 'proveedor'
            ],
            [
                'nombre_comercial' => 'Doble Vela',
                'slug' => 'doble-vela',
                'razon_social' => 'Doble Vela S.A.',
                'tipo' => 'proveedor'
            ],
            [
                'nombre_comercial' => '4Promotional',
                'slug' => '4promotional',
                'razon_social' => '4Promotional S.A.',
                'tipo' => 'proveedor'
            ],
        ];

        foreach ($partners as $partner) {
            Partner::firstOrCreate(
                ['slug' => $partner['slug']], // usamos el slug como clave única
                $partner
            );
        }
    }
}
