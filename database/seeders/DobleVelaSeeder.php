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

class DobleVelaSeeder extends Seeder
{
    public function run(): void
    {
        // Dueño/publicador (Printec) y proveedor (Doble Vela)
        $publisher = Partner::where('slug', 'printec')->firstOrFail();   // owner_id = 1 (público)
        $provider  = Partner::where('slug', 'doble-vela')->firstOrFail(); // partner_id = proveedor

        // Usuario creador (fallback a 1)
        $createdBy = User::where('email', 'ebutron@printec.mx')->value('id')
            ?? User::where('email', 'ingrid@printec.mx')->value('id')
            ?? 1;

        // JSON
        if (!Storage::exists('doblevela/products.json')) {
            $this->command?->error('Archivo no encontrado: storage/app/doblevela/products.json');
            return;
        }
        $rows = json_decode(Storage::get('doblevela/products.json'), true) ?? [];
        if (empty($rows)) {
            $this->command?->warn('JSON vacío o inválido: doblevela/products.json');
            return;
        }

        // Almacenes del proveedor (códigos normalizados: "7","8","9","10","20","24")
        $dvWarehouses = ProductWarehouse::where('partner_id', $provider->id)->get()
            ->keyBy(fn ($w) => (string) $w->codigo);

        // Agrupar por MODELO
        $grouped = collect($rows)->groupBy(fn ($r) => trim($r['MODELO'] ?? ''));

        $created = 0; $updated = 0; $variants = 0; $stocks = 0;

        foreach ($grouped as $model => $items) {
            if (!$model) continue;

            DB::transaction(function () use (
                $model, $items, $provider, $publisher, $createdBy, $dvWarehouses,
                &$created, &$updated, &$variants, &$stocks
            ) {
                $first = collect($items)->first();

                // ===== Categoría (del proveedor)
                $familia = trim($first['Familia'] ?? 'Sin familia');
                $subfam  = trim($first['SubFamilia'] ?? null);
                $catSlug = Str::slug($familia);

                $productCategory = ProductCategory::firstOrCreate(
                    ['slug' => $catSlug, 'partner_id' => $provider->id],
                    ['name' => $familia, 'subcategory' => $subfam]
                );

                // ===== Producto (owner=Printec, partner=DV)
                $slug = 'dv-' . Str::slug($model);

                $product = Product::updateOrCreate(
                    ['slug' => $slug, 'partner_id' => $provider->id],
                    [
                        'owner_id'           => $publisher->id,
                        'model_code'         => $model,
                        'name'               => $first['Nombre Corto'] ?? ($first['NOMBRE'] ?? $model),
                        'price'              => (float) ($first['Price'] ?? 0),
                        'description'        => $first['Descripcion'] ?? null,
                        'short_description'  => $first['Nombre Corto'] ?? null,
                        'material'           => $first['Material'] ?? null,
                        'packing_type'       => $first['Tipo Empaque'] ?? null,
                        'unit_package'       => $first['Unidad Empaque'] ?? null,
                        'box_size'           => $first['Medida Caja Master'] ?? null,
                        'box_weight'         => $first['Peso caja'] ?? null,
                        'product_weight'     => $first['Peso Producto'] ?? null,
                        'product_size'       => $first['Medida Producto'] ?? null,
                        'meta_description'   => $first['Nombre Corto'] ?? null,
                        'meta_keywords'      => $first['Tipo Impresion'] ?? null,
                        'featured'           => false,
                        'new'                => false,
                        'product_category_id'=> $productCategory->id,
                        'partner_id'         => $provider->id,
                        'owner_id'           => $publisher->id,
                        'created_by'         => $createdBy,
                        'is_active'          => true,
                        // NUEVOS CAMPOS
                        'is_own_product'     => false, // Es producto de proveedor
                        'is_public'          => true,  // Visible para todos
                    ]
                );

                $product->wasRecentlyCreated ? $created++ : $updated++;

                // ===== Imagen principal del producto
                $mainImageName = "{$model}_lrg.jpg";
                $mainLocalPath = "products/doblevela/{$mainImageName}";
                $this->downloadImageIfNeeded(
                    "https://www.doblevela.com/images/large/" . rawurlencode($mainImageName),
                    storage_path("app/public/{$mainLocalPath}")
                );
                if ($product->main_image !== $mainLocalPath) {
                    $product->main_image = $mainLocalPath;
                    $product->save();
                }

                // ===== Técnicas de impresión (si existe la tabla)
                $this->upsertImpressionTechniques($product->id, trim($first['Tipo Impresion'] ?? ''));

                // ===== Variantes + stock por almacén del proveedor
                foreach ($items as $row) {
                    $sku = trim($row['CLAVE'] ?? '');
                    if ($sku === '') continue;

                    // Color: "04 - ROJO" => "ROJO"
                    $colorName = trim(preg_replace('/^\d+\s*-\s*/', '', (string)($row['COLOR'] ?? '')));
                    $colorName  = Str::of($colorName)->lower();
                    $colorKey  = Str::of($colorName)->lower()->replace(' ', '')->toString();

                    // Imagen variante
                    $variantImageName  = Str::of($model)->replace(' ', '')->append('_', $colorKey, '_lrg.jpg')->toString();
                    $variantLocalPath  = "products/doblevela/{$variantImageName}";
                    $this->downloadImageIfNeeded(
                        "https://www.doblevela.com/images/large/" . rawurlencode($variantImageName),
                        storage_path("app/public/{$variantLocalPath}")
                    );

                    // Upsert variante
                    $variant = ProductVariant::updateOrCreate(
                        ['sku' => $sku],
                        [
                            'product_id' => $product->id,
                            'slug'       => Str::slug($sku),
                            'code_name'  => $sku,
                            'color_name' => $colorName ?: null,
                            'image'      => $variantLocalPath,
                        ]
                    );
                    $variants++;

                    // Stock por almacén (Disponible - Comprometido)
                    foreach ($dvWarehouses as $code => $warehouse) {
                        $dispKey = "Disponible Almacen {$code}";
                        $compKey = "Comprometido Almacen {$code}";
                        $disp    = (int)($row[$dispKey] ?? 0);
                        $comp    = (int)($row[$compKey] ?? 0);
                        $qty     = max(0, $disp - $comp);

                        ProductStock::updateOrCreate(
                            ['variant_id' => $variant->id, 'warehouse_id' => $warehouse->id],
                            ['stock' => $qty]
                        );
                        $stocks++;
                    }
                }
            });
        }

        $this->command?->info("Doble Vela → productos creados/act: {$created}/{$updated}, variantes: {$variants}, stocks: {$stocks}.");
    }

    private function upsertImpressionTechniques(int $productId, string $codes): void
    {
        if (!Schema::hasTable('product_impression_technique') || $codes === '') return;

        $map = [
            'SE' => 'Serigrafía',
            'BR' => 'Bordado',
            'TR' => 'Transfer',
            'TM' => 'Tampografía',
            'FC' => 'Full Color',
            'LB' => 'Láser',
            'GR' => 'Grabado',
        ];

        $tokens = collect(preg_split('/[\s,;|]+/', $codes, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn($c) => strtoupper(trim($c)))
            ->unique()
            ->values();

        foreach ($tokens as $code) {
            DB::table('product_impression_technique')->updateOrInsert(
                ['product_id' => $productId, 'code' => $code],
                ['name' => $map[$code] ?? $code, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // (Opcional) Sync: eliminar técnicas que ya no aparecen
        $existing = DB::table('product_impression_technique')
            ->where('product_id', $productId)->pluck('code')->toArray();

        $toDelete = array_diff($existing, $tokens->all());
        if ($toDelete) {
            DB::table('product_impression_technique')
                ->where('product_id', $productId)
                ->whereIn('code', $toDelete)
                ->delete();
        }
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
