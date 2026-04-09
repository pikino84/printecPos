<?php

namespace App\Services\DobleVela;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DobleVelaService
{
    protected DobleVelaClient $client;

    public function __construct(DobleVelaClient $client)
    {
        $this->client = $client;
    }

    /**
     * Sincronización completa de productos (GetExistenciaAll).
     * Solo disponible de 8PM a 8AM CDMX.
     *
     * @return array|null Array de productos o null si hay error
     */
    public function syncProducts(): ?array
    {
        $response = $this->client->getExistenciaAll();

        if (($response['intCodigo'] ?? -1) !== 0) {
            $mensaje = $response['strMensaje'] ?? 'Error desconocido';
            Log::error("DobleVela API error: [{$response['intCodigo']}] {$mensaje}");
            return null;
        }

        $products = $response['Resultado'] ?? [];
        if (empty($products)) {
            Log::warning('DobleVela: respuesta exitosa pero sin productos');
            return null;
        }

        // Guardar JSON para los seeders
        $this->ensureDirectoryExists();
        Storage::put(
            'doblevela/products.json',
            json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        Log::info('DobleVela: productos sincronizados', ['count' => count($products)]);

        return $products;
    }

    /**
     * Consulta stock en tiempo real de un modelo (GetExistencia).
     * Disponible 24 horas. Recomendado para detalle de producto.
     *
     * @param string $modelo Código de MODELO
     * @return array|null Array de variantes con stock o null si error
     */
    public function getRealtimeStock(string $modelo): ?array
    {
        // Cache por 5 minutos para no saturar el API
        $cacheKey = "doblevela_stock_{$modelo}";

        return Cache::remember($cacheKey, 300, function () use ($modelo) {
            try {
                $response = $this->client->getExistencia($modelo);

                if (($response['intCodigo'] ?? -1) !== 0) {
                    Log::warning("DobleVela GetExistencia error para {$modelo}", [
                        'codigo' => $response['intCodigo'] ?? null,
                        'mensaje' => $response['strMensaje'] ?? null,
                    ]);
                    return null;
                }

                return $response['Resultado'] ?? [];
            } catch (\Exception $e) {
                Log::error("DobleVela GetExistencia fallo para {$modelo}: {$e->getMessage()}");
                return null;
            }
        });
    }

    /**
     * Descarga imágenes de modelos via API (GetrProdImagenes).
     * Acepta 1-20 modelos por consulta. Retorna imágenes en base64.
     *
     * @param array $modelos Array de códigos de modelo
     * @return array|null Respuesta con CLAVES y MODELOS
     */
    public function getProductImages(array $modelos): ?array
    {
        try {
            $response = $this->client->getProductImagenes($modelos);

            if (($response['intCodigo'] ?? -1) !== 0) {
                Log::warning('DobleVela GetrProdImagenes error', [
                    'codigo' => $response['intCodigo'] ?? null,
                    'mensaje' => $response['strMensaje'] ?? null,
                ]);
                return null;
            }

            return $response['Resultado'] ?? [];
        } catch (\Exception $e) {
            Log::error("DobleVela GetrProdImagenes fallo: {$e->getMessage()}");
            return null;
        }
    }

    private function ensureDirectoryExists(): void
    {
        $path = storage_path('app/doblevela');
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }
}
