<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DobleVela\DobleVelaService;
use App\Services\DobleVela\DobleVelaDataImporter;

class SyncDobleVelaProducts extends Command
{
    protected $signature = 'sync:doblevela-products';
    protected $description = 'Sincroniza productos desde el API de Doble Vela y los almacena en la base de datos';

    public function handle(DobleVelaService $service, DobleVelaDataImporter $importer)
    {
        $this->info('Conectando al API de Doble Vela para obtener productos...');

        try {
            $service->syncProducts(); // Guarda en storage
            $this->info('Productos guardados exitosamente en storage.');

            $importer->import(); // Procesa y guarda en DB
            $this->info('Productos importados a la base de datos correctamente.');
        } catch (\Exception $e) {
            $this->error("Error al sincronizar productos: " . $e->getMessage());
        }
    }
}
