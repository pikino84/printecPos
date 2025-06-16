<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductWarehouse;
use App\Models\ProductProvider;
use Illuminate\Support\Facades\Storage;

class ProductWarehouseInnovationSeeder extends Seeder
{
    public function run()
    {
        // Cargar el archivo JSON
        $file = Storage::get('innovation/stock.json');
        $products = json_decode($file, true);

        // Buscar el proveedor Innovation
        $provider = ProductProvider::where('slug', 'innovation')->first();

        if (!$provider) {
            throw new \Exception('Proveedor Innovation no encontrado');
        }

        $warehouseNames = [
            'algarin' => 'Innovation Almacén Algarin',
            'cedis' => 'Innovation Almacén Nuevo Cedis',
            'fiscal' => 'Innovation Almacén Fiscal',
            'externo' => 'Innovation Almacén Externo',
            '15' => 'Innovation Almacén 15',
            '16' => 'Innovation Almacén 16',
            '17' => 'Innovation Almacén 17',
            '18' => 'Innovation Almacén 18',
            '19' => 'Innovation Almacén 19',
            '20' => 'Innovation Almacén 20',
            'stock' => 'Innovation Stock General',
            'apartados' => 'Innovation Almacén de Apartado',
        ];

        // Eliminar todos los almacenes anteriores de este proveedor
        ProductWarehouse::where('provider_id', $provider->id)->delete();

        foreach ($warehouseNames as $key => $name) {
            ProductWarehouse::firstOrCreate(
                [
                    'provider_id' => $provider->id,
                    'codigo' => "inno-{$key}",
                ],
                [
                    'name' => $name,
                    'nickname' => null
                ]
            );
        }
        //agregamos a 4promotional por que solo tiene un solo almacén
        $provider4Promotional = ProductProvider::where('slug', '4promotional')->first();
        ProductWarehouse::firstOrCreate(
            [
                'provider_id' => $provider4Promotional->id,
                'codigo' => '4promo-001',
            ],
            [
                'name' => '4Promotional Almacén 001',
                'nickname' => null
            ]
        );

    }
}
