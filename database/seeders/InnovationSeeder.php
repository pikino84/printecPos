<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\ProductStock;

class InnovationSeeder extends Seeder
{
    public function run(): void
    {
        // Dueño/publicador (Printec) y proveedor (Innovation)
        $publisher = Partner::where('slug', 'printec')->firstOrFail();     // owner_id
        $provider  = Partner::where('slug', 'innovation')->firstOrFail();  // partner_id (proveedor real)

        // Usuario creador (fallback a 1)
        $createdBy = User::where('email', 'ebutron@printec.mx')->value('id')
            ?? User::where('email', 'ingrid@printec.mx')->value('id')
            ?? 1;

        // === Cargar JSON ===
        $productsArr   = json_decode(Storage::get('innovation/products.json'), true) ?? [];
        $salePricesArr = json_decode(Storage::get('innovation/sale_prices.json'), true) ?? [];
        $stockArr      = json_decode(Storage::get('innovation/stock.json'), true) ?? [];

        // Normaliza si vienen en 'data' o no
        $products   = collect($productsArr['data'] ?? $productsArr);
        $salePrices = collect($salePricesArr['data'] ?? $salePricesArr)
                        ->keyBy(fn($r) => $r['codigo'] ?? ($r['idProducto'] ?? null));
        $stockByKey = collect($stockArr['data'] ?? $stockArr)
                        ->keyBy(fn($r) => $r['codigo'] ?? ($r['idProducto'] ?? null));

        // Almacenes Innovation (por codigo)
        $innoWarehouses = ProductWarehouse::where('partner_id', $provider->id)
            ->get()->keyBy('codigo'); // ej: inno-algarin, inno-cedis, inno-15, etc.

        // Mapa stock_key -> codigo almacén
        $stockKeyToWhCode = [
            'stock_algarin'      => 'algarin',
            'stock_nuevo_cedis'  => 'nuevo_cedis',
            'stock_fiscal'       => 'fiscal',
            'stock_externo'      => 'externo',
            'almacen_15'         => '15',
            'almacen_16'         => '16',
            'almacen_17'         => '17',
            'almacen_18'         => '18',
            'almacen_19'         => '19',
            'almacen_20'         => '20',
            // 'general_stock' y 'apartados' se ignoran
        ];

        $created = 0; $updated = 0; $variants = 0; $stocks = 0; $techs = 0;

        foreach ($products as $p) {
            $codigo = trim($p['codigo'] ?? '');
            if ($codigo === '') continue;

            DB::transaction(function () use (
                $p, $codigo, $provider, $publisher, $createdBy,
                $salePrices, $stockByKey, $innoWarehouses, $stockKeyToWhCode,
                &$created, &$updated, &$variants, &$stocks, &$techs
            ) {
                // ===== Categoría del proveedor
                $catBlock = $p['categorias']['categorias'][0] ?? null;
                $catId    = null;
                if ($catBlock) {
                    $catSlug = Str::slug($catBlock['codigo'] ?? $catBlock['nombre'] ?? 'generico');
                    $cat = ProductCategory::firstOrCreate(
                        ['slug' => $catSlug, 'partner_id' => $provider->id],
                        [
                            'name'        => $catBlock['nombre'] ?? ucfirst($catSlug),
                            'subcategory' => optional($p['categorias'])['subcategorias'][0]['nombre'] ?? null,
                            'is_active'   => 1,
                        ]
                    );
                    $catId = $cat->id;
                }

                // ===== Precio base desde sale_prices: mínimo de precios_venta
                $spRow  = $salePrices->get($codigo) ?? $salePrices->get($p['idProducto'] ?? null);
                $price  = 0.0;
                if ($spRow && isset($spRow['precios_venta'])) {
                    $price = collect($spRow['precios_venta'])
                        ->pluck('precio')->filter()->map(fn($v) => (float)$v)->min() ?? 0.0;
                }

                // ===== Upsert producto (owner=Printec, partner=Innovation)
                $slug = 'inno-' . Str::slug($codigo);
                $product = Product::updateOrCreate(
                    ['slug' => $slug, 'partner_id' => $provider->id],
                    [
                        'owner_id'            => $publisher->id,
                        'model_code'          => $codigo,
                        'name'                => $p['nombre'] ?? $codigo,
                        'price'               => $price,
                        'description'         => $p['descripcion'] ?? null,
                        'short_description'   => null,
                        'material'            => $p['material'] ?? null,
                        'packing_type'        => null,
                        'unit_package'        => $p['cantidad_por_paquete'] ?? null,
                        'box_size'            => $p['medidas_paquete'] ?? null,
                        'box_weight'          => $p['peso_paquete'] ?? null,
                        'product_weight'      => $p['peso_producto'] ?? null,
                        'product_size'        => $p['medidas_producto'] ?? null,
                        'area_print'          => $p['area_impresion'] ?? null,
                        'meta_description'    => $p['meta_description'] ?? null,
                        'meta_keywords'       => $p['meta_keywords'] ?? null,
                        'featured'            => (bool)($p['destacado'] ?? 0),
                        'new'                 => (bool)($p['nuevo'] ?? 0),
                        'product_category_id' => $catId,
                        'partner_id'          => $provider->id,
                        'owner_id'            => $publisher->id,
                        'created_by'          => $createdBy,
                        'is_active'           => (bool)($p['estatus_producto'] ?? 1),
                    ]
                );
                $product->wasRecentlyCreated ? $created++ : $updated++;

                // ===== Imagen principal
                if (!empty($p['imagen_principal'])) {
                    $mainUrl   = str_replace(' ', '%20', $p['imagen_principal']);
                    $imageName = basename(parse_url($mainUrl, PHP_URL_PATH));
                    $localPath = "products/innovation/{$imageName}";
                    $this->downloadImageIfNeeded($mainUrl, storage_path("app/public/{$localPath}"));
                    if ($product->main_image !== $localPath) {
                        $product->main_image = $localPath;
                        $product->save();
                    }
                }

                // ===== Técnicas de impresión
                if (!empty($p['tecnicas_impresion']) && Schema::hasTable('product_impression_technique')) {
                    $incoming = collect($p['tecnicas_impresion'])
                        ->map(fn($t) => [
                            'code' => trim($t['codigo'] ?? ''),
                            'name' => trim($t['nombre'] ?? ($t['codigo'] ?? '')),
                        ])
                        ->filter(fn($t) => $t['code'] !== '')
                        ->unique('code')
                        ->values();

                    foreach ($incoming as $t) {
                        DB::table('product_impression_technique')->updateOrInsert(
                            ['product_id' => $product->id, 'code' => $t['code']],
                            ['name' => $t['name'], 'updated_at' => now(), 'created_at' => now()]
                        );
                        $techs++;
                    }

                    $existing = DB::table('product_impression_technique')
                        ->where('product_id', $product->id)->pluck('code')->toArray();
                    $toDelete = array_diff($existing, $incoming->pluck('code')->all());
                    if ($toDelete) {
                        DB::table('product_impression_technique')
                            ->where('product_id', $product->id)
                            ->whereIn('code', $toDelete)
                            ->delete();
                    }
                }

                // ===== Variantes & Stock
                $colorRows = collect($p['colores'] ?? []);
                $stockRow  = $stockByKey->get($codigo) ?? $stockByKey->get($p['idProducto'] ?? null);
                $existList = collect($stockRow['existencias'] ?? []);

                foreach ($colorRows as $c) {
                    $ex = $existList->firstWhere('clave', $sku);
                    if ($ex) {
                        $tuvoAlmacen = false;

                        // 1) Caso antiguo (por almacén)
                        foreach ($stockKeyToWhCode as $stockKey => $whCode) {
                            if (!array_key_exists($stockKey, $ex)) continue;
                            $qty = (int)$ex[$stockKey];
                            if ($qty < 0) $qty = 0;
                            $warehouse = $innoWarehouses->get($whCode);
                            if (!$warehouse) continue;

                            ProductStock::updateOrCreate(
                                ['variant_id' => $variant->id, 'warehouse_id' => $warehouse->id],
                                ['stock' => $qty]
                            );
                            $stocks++;
                            $tuvoAlmacen = true;
                        }

                        // 2) Caso nuevo (consolidado): general_stock -> “nuevo_cedis”
                        if (!$tuvoAlmacen && isset($ex['general_stock'])) {
                            $qty = max(0, (int)$ex['general_stock']);
                            $warehouse = $innoWarehouses->get('nuevo_cedis'); // tu código real
                            if ($warehouse) {
                                ProductStock::updateOrCreate(
                                    ['variant_id' => $variant->id, 'warehouse_id' => $warehouse->id],
                                    ['stock' => $qty]
                                );
                                $stocks++;
                            } else {
                                $this->command?->warn("Warehouse 'nuevo_cedis' no existe para recibir general_stock (sku {$sku})");
                            }
                        }
                    }
                }
            });
        }

        $this->command?->info("Innovation → productos creados/act: {$created}/{$updated}, variantes: {$variants}, stocks: {$stocks}, técnicas: {$techs}.");
    }

    private function downloadImageIfNeeded(string $url, string $destPath): void
    {
        try {
            $dir = dirname($destPath);
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            if (file_exists($destPath)) return;

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0',
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);
            $img = curl_exec($ch);
            curl_close($ch);

            if ($img !== false && strlen($img) > 0) {
                file_put_contents($destPath, $img);
            } else {
                $this->command?->warn("No se pudo descargar imagen: {$url}");
            }
        } catch (\Throwable $e) {
            $this->command?->warn("Error descargando imagen {$url}: {$e->getMessage()}");
        }
    }
}
