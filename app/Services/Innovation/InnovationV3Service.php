<?php

namespace App\Services\Innovation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InnovationV3Service
{
    protected string $token;
    protected string $user;
    protected string $password;
    protected array $endpoints;
    protected int $limit;

    public function __construct()
    {
        $cfg = config('services.innovation_v3');
        $this->token     = $cfg['auth_token'];
        $this->user      = $cfg['user'];
        $this->password  = $cfg['password'];
        $this->endpoints = $cfg['endpoints'];
        $this->limit     = (int)($cfg['page_limit'] ?? 1500);
    }

    /** Header con auth-token como indica el manual 3.0 */
    private function headers(): array
    {
        return [
            'auth-token'   => $this->token,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent'   => 'PrintecPOS/InnovationV3',
        ];
    }

    /** Parámetros base que exige el WS 3.0 (User, Clave) */
    private function baseParams(): array
    {
        return [
            'User'  => $this->user,
            'Clave' => $this->password,
        ];
    }

    /** GET + reintentos sencillos */
    private function get(string $url, array $query = [], int $tries = 3)
    {
        $query = array_filter($query + $this->baseParams(), fn($v) => $v !== null && $v !== '');
        $resp = Http::withHeaders($this->headers())
            ->retry($tries, 300) // backoff corto
            ->get($url, $query);

        if (!$resp->successful()) {
            Log::warning("InnovationV3 GET fallo", ['url' => $url, 'status' => $resp->status(), 'body' => $resp->body()]);
        }

        return $resp->json();
    }

    /** Descarga TODOS los productos (paginado) y genera los 3 JSON de compatibilidad */
    public function downloadAll(): void
    {
        [$products, $adapterProducts, $adapterStock] = $this->fetchAllProductsWithAdapters();

        // 1) products.json (formato legacy: data[] con colores[])
        Storage::put('innovation/products.json', json_encode(['data' => $adapterProducts], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        // 2) stock.json (formato legacy: data[] con existencias[] por SKU)
        Storage::put('innovation/stock.json', json_encode(['data' => $adapterStock], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        // 3) sale_prices.json (no existe endpoint en v3 -> dejamos vacío)
        Storage::put('innovation/sale_prices.json', json_encode(['data' => []], JSON_PRETTY_PRINT));

        Log::info("InnovationV3 → productos: ".count($products).' | adaptados: '.count($adapterProducts));
    }

    /** Obtiene GetAllProducts con paginación y los adapta a tu estructura actual */
    private function fetchAllProductsWithAdapters(): array
    {
        $page      = 1;
        $productos = [];
        $adapterProducts = [];
        $adapterStock    = [];

        do {
            $payload = $this->get($this->endpoints['GetAllProducts'], [
                'page'  => $page,
                'limit' => $this->limit,
            ]);

            $batch = $payload['productos'] ?? [];
            $totalPages = (int)($payload['paginas_totales'] ?? 0);

            foreach ($batch as $p) {
                $productos[] = $p;

                // --- Adaptación a products.json (legacy) ---
                // Estructura fuente (ejemplo del manual):
                //  Nombre, Categoria[], ImagenP, Codigo, Variantes[][{ "Codigo Variante","Tono","Imagen","Stock" }], Stock
                $colores = [];
                $existencias = [];

                foreach (($p['Variantes'] ?? []) as $v) {
                    $sku   = trim($v['Codigo Variante'] ?? '');
                    $tono  = $v['Tono'] ?? null;
                    $vimg  = $this->normalizeImage($v['Imagen'] ?? null);
                    $vstk  = (int)($v['Stock'] ?? 0);

                    if ($sku === '') continue;

                    // colores[] para products.json
                    $colores[] = [
                        'clave' => $sku,
                        'color' => $tono,
                        'image' => $vimg,
                    ];

                    // existencias[] para stock.json (sin almacenes -> consolidado)
                    $existencias[] = [
                        'clave'          => $sku,
                        'general_stock'  => $vstk, // clave consolidada
                    ];
                }

                $adapterProducts[] = [
                    'codigo'       => $p['Codigo'] ?? null,
                    'nombre'       => $p['Nombre'] ?? null,
                    'descripcion'  => null,
                    'imagen_principal' => $this->normalizeImage($p['ImagenP'] ?? null),
                    'categorias'   => [
                        'categorias'     => array_map(fn($c) => ['nombre'=>$c, 'codigo'=>$c], ($p['Categoria'] ?? [])),
                        'subcategorias'  => [],
                    ],
                    'colores'      => $colores,
                    // puedes agregar más campos si los necesitas
                ];

                $adapterStock[] = [
                    'codigo'       => $p['Codigo'] ?? null,
                    'existencias'  => $existencias,
                ];
            }

            $page++;
        } while (!empty($batch) && ($totalPages === 0 || $page <= $totalPages));

        return [$productos, $adapterProducts, $adapterStock];
    }

    /** Normaliza URLs que vienen con // o espacios según ejemplos del manual */
    private function normalizeImage(?string $url): ?string
    {
        if (!$url) return null;
        $url = str_replace(' ', '%20', $url);
        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }
        return $url;
    }

    // ---- Extras por si los usas individualmente ----

    public function getProducto(string $codigo): ?array
    {
        $json = $this->get($this->endpoints['GetProducto'], ['Codigo' => $codigo]);
        return $json['producto']['Producto'][0] ?? null;
    }

    public function getAllVariantes(int $page = 1, int $limit = 100): array
    {
        $json = $this->get($this->endpoints['GetAllVariantes'], compact('page','limit'));
        return $json['variantes'] ?? [];
    }

    public function getAllProductsLight(): array
    {
        $json = $this->get($this->endpoints['GetAllProductslight']);
        // este endpoint es “resumido”; úsalo si quieres sincronizaciones rápidas
        return $json ?? [];
    }
}
