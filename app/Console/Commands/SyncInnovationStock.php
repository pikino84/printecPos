<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Innovation\InnovationService;

class SyncInnovationStock extends Command
{
    protected $signature = 'innovation:sync-stock';
    protected $description = 'Sincroniza el stock de Innovation desde el API y lo guarda en un archivo JSON';

    public function handle(InnovationService $service)
    {
        $this->info('Conectando al API de Innovation para obtener el stock...');

        try {
            $response = $service->getStock();

            if (is_array($response)) {
                $this->info('Stock sincronizado y guardado correctamente.');
            } else {
                $this->error('La respuesta del API no es válida.');
            }
        } catch (\Exception $e) {
            $this->error('Error al obtener el stock: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
