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
            'connection_timeout' => 60,
            'default_socket_timeout' => 300,
        ];
        
        try {
            $client = new \SoapClient($wsdl, $options);
            
            // Listar métodos disponibles (para debug)
            $functions = $client->__getFunctions();
            $this->line('📋 Métodos SOAP disponibles: ' . count($functions));
            
            // Llamar al método GetExistenciaAll
            $this->info('📡 Solicitando productos con GetExistenciaAll...');
            $this->line("   → Key: " . substr($key, 0, 5) . '***' . substr($key, -5));
            
            // Probar diferentes nombres de parámetro
            $paramNames = ['strKey', 'Key', 'key', 'sKey', 'APIKey'];
            $response = null;
            $lastError = null;
            
            foreach ($paramNames as $paramName) {
                try {
                    $this->line("   → Probando parámetro: {$paramName}");
                    $response = $client->GetExistenciaAll([
                        $paramName => $key
                    ]);
                    
                    // Verificar si la respuesta tiene error de key
                    $responseArray = json_decode(json_encode($response), true);
                    if (isset($responseArray['GetExistenciaAllResult'])) {
                        $result = $responseArray['GetExistenciaAllResult'];
                        if (is_string($result)) {
                            $decoded = json_decode($result, true);
                            if (isset($decoded['strMensaje']) && stripos($decoded['strMensaje'], 'key') !== false) {
                                $this->warn("   ⚠️ {$paramName}: {$decoded['strMensaje']}");
                                continue; // Probar siguiente nombre
                            }
                            if (isset($decoded['Resultado']) && !empty($decoded['Resultado'])) {
                                $this->info("   ✅ Parámetro correcto: {$paramName}");
                                break;
                            }
                        }
                    }
                    
                    // Si llegamos aquí sin error, usar esta respuesta
                    $this->info("   ✅ Parámetro correcto: {$paramName}");
                    break;
                    
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    $this->warn("   ⚠️ {$paramName} falló: " . substr($lastError, 0, 50));
                }
            }
            
            if ($response === null) {
                throw new \Exception('No se pudo conectar con ningún nombre de parámetro. Último error: ' . $lastError);
            }
            
            $this->info('✅ Respuesta recibida del API');
            
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
        // Debug: mostrar estructura de respuesta
        $this->line('🔍 Analizando estructura de respuesta...');
        
        // Si ya es array, retornarlo
        if (is_array($response)) {
            $this->line('   → Respuesta es array directo');
            return $response;
        }
        
        // Si es objeto, convertir a array
        if (is_object($response)) {
            $responseArray = json_decode(json_encode($response), true);
            
            // Mostrar keys disponibles para debug
            $this->line('   → Keys en respuesta: ' . implode(', ', array_keys($responseArray)));
            
            // Buscar GetExistenciaAllResult (estructura típica de SOAP .NET)
            if (isset($responseArray['GetExistenciaAllResult'])) {
                $result = $responseArray['GetExistenciaAllResult'];
                $this->line('   → Encontrado GetExistenciaAllResult');
                
                // Puede ser string JSON o array
                if (is_string($result)) {
                    $decoded = json_decode($result, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $decoded;
                    }
                }
                
                if (is_array($result)) {
                    return $result;
                }
            }
            
            // Buscar otras estructuras comunes
            $keysToTry = ['Result', 'result', 'return', 'data', 'productos', 'Productos', 'items'];
            foreach ($keysToTry as $key) {
                if (isset($responseArray[$key])) {
                    $this->line("   → Encontrado key: {$key}");
                    $data = $responseArray[$key];
                    
                    if (is_string($data)) {
                        $decoded = json_decode($data, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            return $decoded;
                        }
                    }
                    
                    if (is_array($data)) {
                        return $data;
                    }
                }
            }
            
            // Si la respuesta tiene un solo key con array grande
            foreach ($responseArray as $key => $value) {
                if (is_array($value) && count($value) > 5) {
                    $this->line("   → Usando array grande en key: {$key} (" . count($value) . " items)");
                    return $value;
                }
                // Si es string, intentar decodificar JSON
                if (is_string($value) && strlen($value) > 100) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $this->line("   → Decodificado JSON de key: {$key} (" . count($decoded) . " items)");
                        return $decoded;
                    }
                }
            }
            
            // Último recurso: devolver el array completo
            if (!empty($responseArray)) {
                return $responseArray;
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
     * Horarios DISPONIBLES (CDMX):
     * - 09:00 - 10:00
     * - 13:00 - 14:00  
     * - 17:00 - 18:00
     */
    private function isApiAvailable(): bool
    {
        // Obtener hora actual en CDMX
        $cdmxNow = Carbon::now('America/Mexico_City');
        $cdmxHour = $cdmxNow->hour;
        $cdmxMinute = $cdmxNow->minute;
        
        // Ventanas de disponibilidad (hora inicio, hora fin)
        $availableWindows = [
            [9, 10],   // 09:00 - 10:00
            [13, 14],  // 13:00 - 14:00
            [17, 18],  // 17:00 - 18:00
        ];
        
        $isAvailable = false;
        foreach ($availableWindows as [$start, $end]) {
            if ($cdmxHour >= $start && $cdmxHour < $end) {
                $isAvailable = true;
                break;
            }
        }
        
        $cancunNow = Carbon::now('America/Cancun');
        
        if (!$isAvailable) {
            $this->warn(sprintf(
                '⏰ API no disponible - Hora actual: %s Cancún (%s CDMX)',
                $cancunNow->format('H:i'),
                $cdmxNow->format('H:i')
            ));
            $this->line('   Horarios disponibles (CDMX): 09:00-10:00, 13:00-14:00, 17:00-18:00');
        } else {
            $this->info(sprintf(
                '✅ API disponible - Hora actual: %s Cancún (%s CDMX)',
                $cancunNow->format('H:i'),
                $cdmxNow->format('H:i')
            ));
        }
        
        return $isAvailable;
    }
}