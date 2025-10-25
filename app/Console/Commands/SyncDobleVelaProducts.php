<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SyncDobleVelaProducts extends Command
{
    protected $signature = 'sync:doblevela-products {--force : Forzar ejecución incluso si API está bloqueada}';
    protected $description = 'Sincroniza productos de Doble Vela (API disponible 7PM-9AM CDMX)';

    public function handle()
    {
        $startTime = now();
        $this->info('🔄 Iniciando sincronización Doble Vela - ' . $startTime->format('Y-m-d H:i:s T'));
        
        // Verificar si API está disponible (no es horario laboral CDMX)
        if (!$this->option('force') && !$this->isApiAvailable()) {
            $this->warn('⏰ API no disponible en este horario (bloqueada 9AM-7PM CDMX)');
            Log::warning('Intento de sync Doble Vela en horario bloqueado');
            Storage::put('doblevela_last_sync.txt', 'SKIPPED - API bloqueada');
            return 1;
        }
        
        try {
            // 1. Ejecutar comando de descarga API
            $this->info('📥 Descargando productos de API Doble Vela...');
            
            // Ajusta 'sync:doblevela-products' al nombre real de tu comando que genera el JSON
            $exitCode = Artisan::call('sync:doblevela-products-api');
            
            if ($exitCode !== 0) {
                throw new \Exception('Error al descargar productos de API');
            }
            
            $this->info('✅ Productos descargados del API');
            
            // 2. Verificar que el JSON existe y tiene datos
            if (!Storage::exists('doblevela/products.json')) {
                throw new \Exception('Archivo products.json no encontrado');
            }
            
            $jsonData = json_decode(Storage::get('doblevela/products.json'), true);
            if (empty($jsonData)) {
                throw new \Exception('JSON vacío o inválido');
            }
            
            $productCount = count($jsonData);
            $this->info("📦 {$productCount} productos encontrados en JSON");
            
            // 3. Ejecutar seeder
            $this->info('💾 Poblando base de datos...');
            Artisan::call('db:seed', [
                '--class' => 'DobleVelaSeeder',
                '--force' => true
            ]);
            
            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);
            
            $this->info("✅ Sincronización completada en {$duration} segundos");
            
            Log::info('✅ Sincronización Doble Vela completada', [
                'products' => $productCount,
                'duration_seconds' => $duration,
                'timestamp' => $endTime->toDateTimeString(),
                'timezone' => config('app.timezone')
            ]);
            
            Storage::put('doblevela_last_sync.txt', "SUCCESS - {$endTime->toDateTimeString()} - {$productCount} productos");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            
            Log::error('❌ Error en sincronización Doble Vela', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            Storage::put('doblevela_last_sync.txt', "FAILED - {$e->getMessage()}");
            
            return 1;
        }
    }
    
    /**
     * Verificar si la API está disponible
     * API bloqueada: 9AM-7PM CDMX (10AM-8PM Cancún)
     * API disponible: 7PM-9AM CDMX (8PM-10AM Cancún)
     */
    private function isApiAvailable(): bool
    {
        // Obtener hora actual en CDMX
        $cdmxNow = Carbon::now('America/Mexico_City');
        $cdmxHour = $cdmxNow->hour;
        
        // API bloqueada de 9:00 a 18:59 CDMX (10:00 a 19:59 Cancún)
        $isBlocked = ($cdmxHour >= 9 && $cdmxHour < 19);
        
        if ($isBlocked) {
            $cancunNow = Carbon::now('America/Cancun');
            $this->warn(sprintf(
                '⏰ API bloqueada - Hora actual: %s Cancún (%s CDMX)',
                $cancunNow->format('H:i'),
                $cdmxNow->format('H:i')
            ));
        }
        
        return !$isBlocked;
    }
}