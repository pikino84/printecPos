<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductProvider;
use App\Models\ProductWarehouse;

class ProductWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'codigo' => 7,
                'name' => 'Doble Vela Almacén',
                'nickname' => 'C E D I S',
                'provider_slug' => 'doble-vela',
            ],
            [
                'codigo' => 15,
                'name' => 'Almacén General',
                'nickname' => 'ALGARÍN',
                'provider_slug' => 'innovation',
            ],
            [
                'codigo' => 16,
                'name' => 'Almacén 16',
                'nickname' => 'M T Y',
                'provider_slug' => 'innovation',
            ],
            [
                'codigo' => 17,
                'name' => 'Almacén 17',
                'nickname' => 'CDMX',
                'provider_slug' => 'innovation',
            ],
            [
                'codigo' => 18,
                'name' => 'Almacén 18',
                'nickname' => 'CANCUN',
                'provider_slug' => 'innovation',
            ],
            [
                'codigo' => 19,
                'name' => 'Almacén 19',
                'nickname' => 'EXTERNO 24 HR',
                'provider_slug' => 'innovation',
            ],
            [
                'codigo' => 20,
                'name' => 'Almacén 20',
                'nickname' => null,
                'provider_slug' => 'innovation',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            $provider = ProductProvider::where('slug', $warehouse['provider_slug'])->first();

            if ($provider) {
                ProductWarehouse::firstOrCreate(
                    [
                        'codigo' => $warehouse['codigo'],
                        'provider_id' => $provider->id,
                    ],
                    [
                        'name' => $warehouse['name'],
                        'nickname' => $warehouse['nickname'],
                    ]
                );
            }
        }
    }
}
