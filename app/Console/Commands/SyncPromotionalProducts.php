<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SyncPromotionalProducts extends Command
{
    protected $signature = 'sync:promotional-products';
    protected $description = 'Descarga productos de 4promotional.net y guarda el JSON en storage/app/promotional/products.json';

    public function handle()
    {
        $url = 'https://4promotional.net:9090/WsEstrategia/inventario';

        try {
            $response = Http::withOptions([
                'verify' => false
            ])->get($url);


            if ($response->failed()) {
                $this->error('Error al conectarse con 4Promotional');
                return 1;
            }

            $data = $response->json();

            // Crear carpeta si no existe
            $path = storage_path('app/4promotional');
            if (!file_exists($path)) {
                mkdir($path, 0775, true);
            }

            // Guardar archivo
            file_put_contents($path . '/products.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info('Productos de 4Promotional descargados correctamente en: storage/app/4promotional/products.json');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
