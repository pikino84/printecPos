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
    // Contadores para reporte
    private int $productsCreated = 0;
    private int $productsUpdated = 0;
    private int $productsDeactivated = 0;
    private int $variantsCreated = 0;
    private int $variantsUpdated = 0;
    private int $variantsDeactivated = 0;
    private int $stocksUpdated = 0;

    public function run(): void
    {
        // Dueño/publicador (Printec) y proveedor (Doble Vela)
        $publisher = Partner::where('slug', 'printec')->firstOrFail();
        $provider  = Partner::where('slug', 'doble-vela')->firstOrFail();

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

        // Almacenes del proveedor
        $dvWarehouses = ProductWarehouse::where('partner_id', $provider->id)->get()
            ->keyBy(fn ($w) => (string) $w->codigo);

        // Agrupar por MODELO
        $grouped = collect($rows)->groupBy(fn ($r) => trim($r['MODELO'] ?? ''));

        // Recolectar SKUs y slugs que vienen en el JSON (para detectar eliminados)
        $jsonSlugs = [];
        $jsonSkus = [];

        foreach ($grouped as $model => $items) {
            if (!$model) continue;

            $slug = 'dv-' . Str::slug($model);
            $jsonSlugs[] = $slug;

            foreach ($items as $row) {
                $sku = trim($row['CLAVE'] ?? '');
                if ($sku !== '') {
                    $jsonSkus[] = $sku;
                }
            }
        }

        // Procesar productos
        foreach ($grouped as $model => $items) {
            if (!$model) continue;

            DB::transaction(function () use (
                $model, $items, $provider, $publisher, $createdBy, $dvWarehouses
            ) {
                $first = collect($items)->first();
                $slug = 'dv-' . Str::slug($model);

                // Buscar si el producto ya existe
                $existingProduct = Product::where('slug', $slug)
                    ->where('partner_id', $provider->id)
                    ->first();

                if ($existingProduct) {
                    // ════════════════════════════════════════════════════════
                    // PRODUCTO EXISTENTE: Solo actualizar precio
                    // ════════════════════════════════════════════════════════
                    $existingProduct->update([
                        'price' => (float) ($first['Price'] ?? $existingProduct->price),
                    ]);
                    
                    $product = $existingProduct;
                    $this->productsUpdated++;
                    
                } else {
                    // ════════════════════════════════════════════════════════
                    // PRODUCTO NUEVO: Crear con todos los datos
                    // ════════════════════════════════════════════════════════
                    $familia = trim($first['Familia'] ?? 'Sin familia');
                    $subfam  = trim($first['SubFamilia'] ?? null);
                    $catSlug = Str::slug($familia);

                    $productCategory = ProductCategory::firstOrCreate(
                        ['slug' => $catSlug, 'partner_id' => $provider->id],
                        ['name' => $familia, 'subcategory' => $subfam]
                    );

                    $product = Product::create([
                        'slug'               => $slug,
                        'partner_id'         => $provider->id,
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
                        'created_by'         => $createdBy,
                        'is_active'          => true,
                        'is_own_product'     => false,
                        'is_public'          => true,
                    ]);

                    $this->productsCreated++;

                    // Imagen principal solo para productos nuevos
                    $mainImageName = "{$model}_lrg.jpg";
                    $mainLocalPath = "products/doblevela/{$mainImageName}";
                    $this->downloadImageIfNeeded(
                        "https://www.doblevela.com/images/large/" . rawurlencode($mainImageName),
                        storage_path("app/public/{$mainLocalPath}")
                    );
                    $product->update(['main_image' => $mainLocalPath]);

                    // Técnicas de impresión solo para productos nuevos
                    $this->upsertImpressionTechniques($product->id, trim($first['Tipo Impresion'] ?? ''));
                }

                // ════════════════════════════════════════════════════════
                // VARIANTES: Actualizar precio y stock (existentes y nuevas)
                // ════════════════════════════════════════════════════════
                foreach ($items as $row) {
                    $sku = trim($row['CLAVE'] ?? '');
                    if ($sku === '') continue;

                    $colorName = trim(preg_replace('/^\d+\s*-\s*/', '', (string)($row['COLOR'] ?? '')));
                    $colorName = Str::of($colorName)->lower()->toString();
                    $colorKey  = Str::of($colorName)->lower()->replace(' ', '')->toString();

                    // Buscar variante existente
                    $existingVariant = ProductVariant::where('sku', $sku)->first();

                    if ($existingVariant) {
                        // Variante existente: solo actualizar precio
                        $existingVariant->update([
                            'price' => (float) ($row['Price'] ?? $existingVariant->price),
                        ]);
                        $variant = $existingVariant;
                        $this->variantsUpdated++;
                    } else {
                        // Variante nueva: crear con todos los datos
                        $variantImageName = Str::of($model)->replace(' ', '')->append('_', $colorKey, '_lrg.jpg')->toString();
                        $variantLocalPath = "products/doblevela/{$variantImageName}";
                        $this->downloadImageIfNeeded(
                            "https://www.doblevela.com/images/large/" . rawurlencode($variantImageName),
                            storage_path("app/public/{$variantLocalPath}")
                        );

                        $variant = ProductVariant::create([
                            'sku'        => $sku,
                            'product_id' => $product->id,
                            'slug'       => Str::slug($sku),
                            'code_name'  => $sku,
                            'color_name' => $colorName ?: null,
                            'image'      => $variantLocalPath,
                            'price'      => (float) ($row['Price'] ?? $product->price),
                        ]);
                        $this->variantsCreated++;
                    }

                    // ════════════════════════════════════════════════════════
                    // STOCK: Siempre actualizar (Disponible - Comprometido)
                    // ════════════════════════════════════════════════════════
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
                        $this->stocksUpdated++;
                    }
                }
            });
        }

        // ════════════════════════════════════════════════════════
        // DESACTIVAR productos y variantes que ya no existen en JSON
        // ════════════════════════════════════════════════════════
        $this->deactivateRemovedItems($provider->id, $jsonSlugs, $jsonSkus);

        // Reporte final
        $this->command?->info("═══════════════════════════════════════════════════════");
        $this->command?->info("📦 Doble Vela - Sincronización completada:");
        $this->command?->info("   Productos creados:      {$this->productsCreated}");
        $this->command?->info("   Productos actualizados: {$this->productsUpdated}");
        $this->command?->info("   Productos desactivados: {$this->productsDeactivated}");
        $this->command?->info("   Variantes creadas:      {$this->variantsCreated}");
        $this->command?->info("   Variantes actualizadas: {$this->variantsUpdated}");
        $this->command?->info("   Variantes desactivadas: {$this->variantsDeactivated}");
        $this->command?->info("   Stocks actualizados:    {$this->stocksUpdated}");
        $this->command?->info("═══════════════════════════════════════════════════════");
    }

    /**
     * Desactivar productos y variantes que ya no vienen en el JSON
     */
    private function deactivateRemovedItems(int $providerId, array $jsonSlugs, array $jsonSkus): void
    {
        // Desactivar productos que ya no existen en JSON
        $deactivatedProducts = Product::where('partner_id', $providerId)
            ->where('is_own_product', false)
            ->where('is_active', true)
            ->whereNotIn('slug', $jsonSlugs)
            ->update(['is_active' => false]);
        
        $this->productsDeactivated = $deactivatedProducts;

        if ($deactivatedProducts > 0) {
            $this->command?->warn("⚠️  {$deactivatedProducts} productos desactivados (ya no existen en API)");
        }

        // Desactivar variantes que ya no existen en JSON
        // Primero obtener IDs de productos de Doble Vela
        $dvProductIds = Product::where('partner_id', $providerId)
            ->where('is_own_product', false)
            ->pluck('id');

        // Poner stock en 0 para variantes que ya no existen
        $removedVariants = ProductVariant::whereIn('product_id', $dvProductIds)
            ->whereNotIn('sku', $jsonSkus)
            ->get();

        foreach ($removedVariants as $variant) {
            // Poner todo el stock en 0
            ProductStock::where('variant_id', $variant->id)->update(['stock' => 0]);
            $this->variantsDeactivated++;
        }

        if ($this->variantsDeactivated > 0) {
            $this->command?->warn("⚠️  {$this->variantsDeactivated} variantes con stock en 0 (ya no existen en API)");
        }
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
            }
        } catch (\Throwable $e) {
            // Silenciar errores de imágenes
        }
    }
}