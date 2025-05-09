<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Provider;

class SyncPromotionalProducts extends Command
{
    protected $signature = 'sync:promotional-products';
    protected $description = 'Sincroniza productos desde 4promotional.net';

    public function handle()
    {
        $url = 'https://4promotional.net:9090/WsEstrategia/inventario';

        try {
            $response = Http::get($url);
            if ($response->failed()) {
                $this->error('No se pudo conectar con el proveedor.');
                return 1;
            }

            $products = $response->json();

            $provider = Provider::firstOrCreate(
                ['name' => '4Promotional'],
                ['nickname' => 'promotional']
            );

            foreach ($products as $item) {
                Product::updateOrCreate(
                    ['external_id' => $item['id']], // Suponiendo que el JSON tiene un campo 'id'
                    [
                        'name' => $item['nombre'],
                        'description' => $item['descripcion'] ?? '',
                        'price' => $item['precio'] ?? 0,
                        'stock' => $item['existencia'] ?? 0,
                        'provider_id' => $provider->id,
                        // Agrega más campos aquí si están disponibles
                    ]
                );
            }

            $this->info('Productos sincronizados exitosamente.');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
