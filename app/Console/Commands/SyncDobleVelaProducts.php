<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\DobleVela\DobleVelaService;

class SyncDobleVelaProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doble-vela:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza productos desde el API de Doble Vela';

    /**
     * Execute the console command.
     */
    public function handle(DobleVelaService $service)
    {
        $this->info('Consultando productos desde Doble Vela...');
        $json = $service->consultarYGuardar();
        $this->info('Datos guardados exitosamente.');
    }
}
