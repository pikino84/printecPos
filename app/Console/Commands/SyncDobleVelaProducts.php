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

        // Log detallado de configuración
        Log::info('🔧 Configuración Doble Vela', [
            'wsdl' => $wsdl,
            'key_presente' => !empty($key),
            'key_length' => strlen($key ?? ''),
            'key_preview' => $key ? (substr($key, 0, 5) . '***' . substr($key, -5)) : 'N/A',
        ]);
        $this->line("🔧 WSDL: {$wsdl}");
        $this->line("🔧 Key length: " . strlen($key ?? '') . " chars");

        if (empty($wsdl) || empty($key)) {
            $errorMsg = 'Configuración DOBLEVELA_WSDL o DOBLEVELA_KEY no definida en .env';
            Log::error($errorMsg, ['wsdl' => $wsdl, 'key_empty' => empty($key)]);
            throw new \Exception($errorMsg);
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
            $this->info('📡 Iniciando conexión SOAP...');
            Log::info('📡 Iniciando conexión SOAP', ['wsdl' => $wsdl]);

            $client = new \SoapClient($wsdl, $options);

            // Listar métodos disponibles (para debug)
            $functions = $client->__getFunctions();
            $this->line('📋 Métodos SOAP disponibles: ' . count($functions));
            Log::info('📋 Métodos SOAP disponibles', ['count' => count($functions), 'methods' => array_slice($functions, 0, 10)]);
            
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
                Log::error('❌ No se pudo conectar con ningún nombre de parámetro', [
                    'ultimo_error' => $lastError,
                    'parametros_probados' => $paramNames,
                ]);
                throw new \Exception('No se pudo conectar con ningún nombre de parámetro. Último error: ' . $lastError);
            }

            $this->info('✅ Respuesta recibida del API');

            // Log detallado de la respuesta raw
            $rawResponse = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $responsePreview = substr($rawResponse, 0, 2000);
            Log::info('📥 Respuesta raw del API (preview)', [
                'response_type' => gettype($response),
                'response_preview' => $responsePreview,
                'response_length' => strlen($rawResponse),
            ]);
            $this->line("📥 Response type: " . gettype($response));
            $this->line("📥 Response length: " . strlen($rawResponse) . " chars");

            // Mostrar las primeras líneas del response para debug
            if (is_object($response)) {
                $responseArray = json_decode(json_encode($response), true);
                $this->line("📥 Response keys: " . implode(', ', array_keys($responseArray)));

                // Si tiene GetExistenciaAllResult, mostrar su contenido
                if (isset($responseArray['GetExistenciaAllResult'])) {
                    $result = $responseArray['GetExistenciaAllResult'];
                    $this->line("📥 GetExistenciaAllResult type: " . gettype($result));
                    if (is_string($result)) {
                        $this->line("📥 GetExistenciaAllResult preview: " . substr($result, 0, 500));
                        Log::info('📥 GetExistenciaAllResult (string)', ['preview' => substr($result, 0, 1000)]);
                    }
                }
            }

            // Verificar si la API rechazó por horario
            $responseArray = json_decode(json_encode($response), true);
            if (isset($responseArray['GetExistenciaAllResult'])) {
                $result = $responseArray['GetExistenciaAllResult'];
                if (is_string($result)) {
                    $decoded = json_decode($result, true);
                    if (isset($decoded['intCodigo']) && $decoded['intCodigo'] !== 0) {
                        $mensaje = $decoded['strMensaje'] ?? 'Error desconocido';
                        Log::warning('⚠️ API Doble Vela rechazó la petición', [
                            'codigo' => $decoded['intCodigo'],
                            'mensaje' => $mensaje,
                        ]);
                        $this->error("⚠️ API rechazó petición: [{$decoded['intCodigo']}] {$mensaje}");
                        throw new \Exception("API Doble Vela: {$mensaje} (código {$decoded['intCodigo']})");
                    }
                }
            }

            // Convertir respuesta a array
            $products = $this->parseResponse($response);

            Log::info('📦 Productos parseados', [
                'count' => count($products),
                'sample' => array_slice($products, 0, 2),
            ]);

            return $products;

        } catch (\SoapFault $e) {
            Log::error('❌ Error SOAP', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'faultcode' => $e->faultcode ?? null,
                'faultstring' => $e->faultstring ?? null,
                'last_request' => $client->__getLastRequest() ?? null,
                'last_response' => $client->__getLastResponse() ?? null,
            ]);
            $this->error("SOAP Fault: " . $e->faultstring ?? $e->getMessage());
            throw new \Exception('Error SOAP: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('❌ Error general en downloadFromSoap', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Parsear respuesta SOAP a array
     */
    private function parseResponse($response): array
    {
        // Debug: mostrar estructura de respuesta
        $this->line('🔍 Analizando estructura de respuesta...');
        Log::info('🔍 Iniciando parseResponse', ['response_type' => gettype($response)]);
        
        // Si ya es array, verificar si tiene Resultado
        if (is_array($response)) {
            if (isset($response['Resultado']) && is_array($response['Resultado'])) {
                $this->line('   → Encontrado array Resultado directo');
                return $response['Resultado'];
            }
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
                        // Verificar si tiene estructura con Resultado
                        if (isset($decoded['Resultado']) && is_array($decoded['Resultado'])) {
                            $this->line('   → Extrayendo array Resultado (' . count($decoded['Resultado']) . ' items)');
                            return $decoded['Resultado'];
                        }
                        return $decoded;
                    }
                }
                
                if (is_array($result)) {
                    // Verificar si tiene estructura con Resultado
                    if (isset($result['Resultado']) && is_array($result['Resultado'])) {
                        $this->line('   → Extrayendo array Resultado (' . count($result['Resultado']) . ' items)');
                        return $result['Resultado'];
                    }
                    return $result;
                }
            }
            
            // Buscar Resultado directamente
            if (isset($responseArray['Resultado']) && is_array($responseArray['Resultado'])) {
                $this->line('   → Encontrado Resultado directo (' . count($responseArray['Resultado']) . ' items)');
                return $responseArray['Resultado'];
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
                            if (isset($decoded['Resultado'])) {
                                return $decoded['Resultado'];
                            }
                            return $decoded;
                        }
                    }
                    
                    if (is_array($data)) {
                        if (isset($data['Resultado'])) {
                            return $data['Resultado'];
                        }
                        return $data;
                    }
                }
            }
            
            // Último recurso: devolver el array completo
            if (!empty($responseArray)) {
                return $responseArray;
            }
        }
        
        Log::error('❌ No se pudo parsear la respuesta SOAP', [
            'response_type' => gettype($response),
            'response_preview' => substr(json_encode($response), 0, 1000),
        ]);
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