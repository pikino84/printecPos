<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\DobleVela\DobleVelaService;
use Carbon\Carbon;

class SyncDobleVelaProducts extends Command
{
    protected $signature = 'sync:doblevela-products {--force : Forzar ejecución incluso si API está bloqueada}';
    protected $description = 'Sincroniza productos de Doble Vela (API disponible 8PM-8AM CDMX)';

    public function handle(DobleVelaService $service)
    {
        $startTime = now();
        $this->info('Iniciando sincronización Doble Vela - ' . $startTime->format('Y-m-d H:i:s T'));

        // Verificar si API está disponible (8PM-8AM CDMX)
        if (!$this->option('force') && !$this->isApiAvailable()) {
            $this->warn('API no disponible en este horario. GetExistenciaAll solo funciona de 8PM a 8AM CDMX.');
            Log::warning('Intento de sync Doble Vela en horario bloqueado (fuera de 8PM-8AM CDMX)');
            Storage::put('doblevela_last_sync.txt', 'SKIPPED - API bloqueada (horario diurno) - ' . now()->toDateTimeString());
            return 1;
        }

        try {
            // 1. Descargar y guardar productos
            $this->info('Conectando a API SOAP Doble Vela (GetExistenciaAll)...');
            $products = $service->syncProducts();

            if ($products === null) {
                throw new \Exception('No se obtuvieron productos de la API. Revisar logs para detalle del error.');
            }

            $productCount = count($products);
            $this->info("{$productCount} productos descargados de la API");

            // 2. Ejecutar seeder de almacenes
            $this->info('Sincronizando almacenes...');
            Artisan::call('db:seed', [
                '--class' => 'ProductWarehouseDobleVelaSeeder',
                '--force' => true,
            ]);
            $this->info(trim(Artisan::output()));

            // 3. Ejecutar seeder principal de productos
            $this->info('Poblando productos en base de datos...');
            Artisan::call('db:seed', [
                '--class' => 'DobleVelaSeeder',
                '--force' => true,
            ]);
            $this->info(trim(Artisan::output()));

            $duration = $startTime->diffInSeconds(now());
            $this->info("Sincronización completada en {$duration} segundos");

            Log::info('Sincronización Doble Vela completada', [
                'products' => $productCount,
                'duration_seconds' => $duration,
            ]);

            Storage::put('doblevela_last_sync.txt', "SUCCESS - " . now()->toDateTimeString() . " - {$productCount} productos - {$duration}s");

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());

            Log::error('Error en sincronización Doble Vela', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Storage::put('doblevela_last_sync.txt', 'FAILED - ' . now()->toDateTimeString() . ' - ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * Verificar si la API GetExistenciaAll está disponible.
     * Según documentación oficial: habilitada SOLO de 8:00 PM a 8:00 AM (CDMX).
     */
    private function isApiAvailable(): bool
    {
        $cdmxNow = Carbon::now('America/Mexico_City');
        $hour = $cdmxNow->hour;

        // Disponible de 20:00 a 07:59 CDMX (8PM a 8AM)
        $isAvailable = $hour >= 20 || $hour < 8;

        $cancunNow = Carbon::now('America/Cancun');

        if ($isAvailable) {
            $this->info(sprintf(
                'API disponible - %s CDMX (%s Cancún)',
                $cdmxNow->format('H:i'),
                $cancunNow->format('H:i')
            ));
        } else {
            $this->warn(sprintf(
                'API no disponible - %s CDMX (%s Cancún) - Horario: 8PM-8AM CDMX',
                $cdmxNow->format('H:i'),
                $cancunNow->format('H:i')
            ));
        }

        return $isAvailable;
    }
}
