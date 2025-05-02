<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Innovation\InnovationService;

class SyncInnovationProducts extends Command
{
    protected $signature = 'sync:innovation-products';

    protected $description = 'Sincroniza productos, stock y precios desde Innovation API';

    public function handle(InnovationService $service)
    {
        $this->info('Validando conexión con la API de Innovation...');
        $validation = $service->validateConnection();

        if (!($validation['response'] ?? false)) {
            $this->error('Fallo en la validación: ' . ($validation['message'] ?? 'Error desconocido'));
            return;
        }

        $this->info('Conexión exitosa. Obteniendo productos...');
        $service->getProducts();
        $this->info('Productos obtenidos y almacenados correctamente.');

        $this->info('Obteniendo stock...');
        $service->getStock();
        $this->info('Stock obtenido y almacenado correctamente.');

        $this->info('Obteniendo precios de venta...');
        $service->getSalePrices();
        $this->info('Precios de venta obtenidos y almacenados correctamente.');
    }
}
