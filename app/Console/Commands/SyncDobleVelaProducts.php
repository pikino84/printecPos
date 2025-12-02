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
            Storage::put('doblevela_last_sync.txt', 'SKIPPED - API bloqueada - ' . now()->toDateTimeString());
            return 1;
        }
        
        try {
            // 1. Descargar productos desde SOAP
            $this->info('📥 Conectando a API SOAP Doble Vela...');
            $products = $this->downloadFromSoap();
            
            if (empty($products)) {
                throw new \Exception('No se obtuvieron productos de la API');
            }
            
            $productCount = count($products);
            $this->info("📦 {$productCount} productos descargados de la API");
            
            // 2. Guardar JSON
            $this->info('💾 Guardando JSON...');
            $this->ensureDirectoryExists();
            Storage::put('doblevela/products.json', json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('✅ JSON guardado en storage/app/doblevela/products.json');
            
            // 3. Ejecutar seeder de almacenes primero
            $this->info('🏭 Sincronizando almacenes...');
            Artisan::call('db:seed', [
                '--class' => 'ProductWarehouseDobleVelaSeeder',
                '--force' => true
            ]);
            $this->info(Artisan::output());
            
            // 4. Ejecutar seeder principal de productos
            $this->info('📦 Poblando productos en base de datos...');
            Artisan::call('db:seed', [
                '--class' => 'DobleVelaSeeder',
                '--force' => true
            ]);
            $this->info(Artisan::output());
            
            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);
            
            $this->info("✅ Sincronización completada en {$duration} segundos");
            
            Log::info('✅ Sincronización Doble Vela completada', [
                'products' => $productCount,
                'duration_seconds' => $duration,
                'timestamp' => $endTime->toDateTimeString(),
                'timezone' => config('app.timezone')
            ]);
            
            Storage::put('doblevela_last_sync.txt', "SUCCESS - {$endTime->toDateTimeString()} - {$productCount} productos - {$duration}s");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            
            Log::error('❌ Error en sincronización Doble Vela', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            Storage::put('doblevela_last_sync.txt', "FAILED - " . now()->toDateTimeString() . " - {$e->getMessage()}");
            
            return 1;
        }
    }
    
    /**
     * Descargar productos desde el servicio SOAP de Doble Vela
     */
    private function downloadFromSoap(): array
    {
        $wsdl = config('services.doblevela.wsdl');
        $key = config('services.doblevela.key');
        
        if (empty($wsdl) || empty($key)) {
            throw new \Exception('Configuración DOBLEVELA_WSDL o DOBLEVELA_KEY no definida en .env');
        }
        
        $this->info("🔗 Conectando a: {$wsdl}");
        
        // Crear cliente SOAP
        $options = [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]),
            'connection_timeout' => 30,
        ];
        
        try {
            $client = new \SoapClient($wsdl, $options);
            
            // Listar métodos disponibles (para debug)
            $functions = $client->__getFunctions();
            $this->line('📋 Métodos SOAP disponibles: ' . count($functions));
            
            // Llamar al método para obtener productos
            // NOTA: Ajusta el nombre del método según la documentación de Doble Vela
            // Posibles nombres: GetProductos, ObtenerProductos, getProducts, etc.
            $this->info('📡 Solicitando productos...');
            
            // Intenta con diferentes nombres de método comunes
            $response = null;
            $methodsToTry = ['GetProductos', 'ObtenerProductos', 'getProducts', 'Productos', 'ListaProductos'];
            
            foreach ($methodsToTry as $method) {
                if (in_array($method, array_map(function($f) {
                    preg_match('/^\w+\s+(\w+)\(/', $f, $m);
                    return $m[1] ?? '';
                }, $functions))) {
                    $this->line("🔄 Intentando método: {$method}");
                    try {
                        $response = $client->$method(['key' => $key]);
                        break;
                    } catch (\Exception $e) {
                        $this->warn("⚠️ Método {$method} falló: " . $e->getMessage());
                    }
                }
            }
            
            // Si no encontró método automáticamente, usa el primero con el key
            if ($response === null) {
                $this->warn('⚠️ No se encontró método automáticamente. Listando métodos disponibles:');
                foreach ($functions as $func) {
                    $this->line("   - {$func}");
                }
                
                // Intento genérico con el primer método que contenga "Product"
                foreach ($functions as $func) {
                    if (stripos($func, 'product') !== false || stripos($func, 'producto') !== false) {
                        preg_match('/^\w+\s+(\w+)\(/', $func, $m);
                        $methodName = $m[1] ?? null;
                        if ($methodName) {
                            $this->info("🔄 Intentando: {$methodName}");
                            $response = $client->$methodName(['key' => $key]);
                            break;
                        }
                    }
                }
            }
            
            if ($response === null) {
                throw new \Exception('No se pudo determinar el método SOAP correcto. Revisa la documentación de Doble Vela.');
            }
            
            // Convertir respuesta a array
            $products = $this->parseResponse($response);
            
            return $products;
            
        } catch (\SoapFault $e) {
            throw new \Exception('Error SOAP: ' . $e->getMessage());
        }
    }
    
    /**
     * Parsear respuesta SOAP a array
     */
    private function parseResponse($response): array
    {
        // Si ya es array, retornarlo
        if (is_array($response)) {
            return $response;
        }
        
        // Si es objeto, convertir a array
        if (is_object($response)) {
            $response = json_decode(json_encode($response), true);
        }
        
        // Buscar el array de productos en la respuesta
        // La estructura puede variar según el WSDL
        if (isset($response['productos'])) {
            return $response['productos'];
        }
        if (isset($response['Productos'])) {
            return $response['Productos'];
        }
        if (isset($response['result'])) {
            return is_array($response['result']) ? $response['result'] : [$response['result']];
        }
        if (isset($response['return'])) {
            return is_array($response['return']) ? $response['return'] : [$response['return']];
        }
        
        // Si la respuesta es directamente el array de productos
        if (is_array($response) && isset($response[0])) {
            return $response;
        }
        
        // Último intento: buscar cualquier array grande en la respuesta
        foreach ($response as $key => $value) {
            if (is_array($value) && count($value) > 10) {
                $this->line("📦 Encontrado array de productos en key: {$key}");
                return $value;
            }
        }
        
        throw new \Exception('No se pudo parsear la respuesta SOAP. Estructura desconocida.');
    }
    
    /**
     * Asegurar que el directorio existe
     */
    private function ensureDirectoryExists(): void
    {
        $path = storage_path('app/doblevela');
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }
    
    /**
     * Verificar si la API está disponible
     * API bloqueada: 9AM-7PM CDMX (horario laboral)
     * API disponible: 7PM-9AM CDMX
     */
    private function isApiAvailable(): bool
    {
        // Obtener hora actual en CDMX
        $cdmxNow = Carbon::now('America/Mexico_City');
        $cdmxHour = $cdmxNow->hour;
        
        // API bloqueada de 9:00 a 18:59 CDMX
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