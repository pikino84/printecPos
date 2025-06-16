<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductWarehouse;
use App\Models\ProductProvider;

class ProductWarehouseDobleVelaSeeder extends Seeder
{
    public function run()
    {
        $file = storage_path('app/doblevela/products.json');
        $products = json_decode(file_get_contents($file), true);

        // Buscar el proveedor Doble Vela
        $provider = ProductProvider::where('slug', 'doble-vela')->first();

        if (!$provider) {
            throw new \Exception('Proveedor Doble Vela no encontrado');
        }

        $warehouses = [];

        // Buscar dinámicamente almacenes
        foreach ($products as $product) {
            foreach ($product as $key => $value) {
                if (preg_match('/^Disponible Almacen (\d+)$/', $key, $matches)) {
                    $codigo = (int) $matches[1];
                    $warehouses[$codigo] = [
                        'name' => "Doble Vela Almacén {$codigo}", // Nombre más claro
                        'nickname' => null
                    ];
                }
            }
        }

        // Insertar almacenes
        foreach ($warehouses as $codigo => $warehouseData) {
            ProductWarehouse::firstOrCreate(
                [
                    'provider_id' => $provider->id,
                    'codigo' => "dv-{$codigo}"                   
                ],
                [
                    'name' => $warehouseData['name'],
                    'nickname' => $warehouseData['nickname']
                ]
            );
        }
    }
}
