<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;

class FourPromotionalSeeder extends Seeder
{
    public function run()
    {
        // 0) Cargar JSON
        if (!Storage::exists('4promotional/products.json')) {
            $this->command?->error('Archivo no encontrado: storage/app/4promotional/products.json');
            return;
        }
        $products = json_decode(Storage::get('4promotional/products.json'), true);

        // 1) Partners
        $owner = Partner::firstOrCreate(
            ['slug' => 'printec'],
            ['nombre_comercial' => 'Printec', 'tipo' => 'asociado']
        );

        $partner = Partner::firstOrCreate(
            ['slug' => '4promotional'],
            [
                'nombre_comercial' => '4Promotional',
                'razon_social'     => '4Promotional S.A. de C.V.',
                'tipo'             => 'proveedor',
            ]
        );

        // 2) Almacén 4Promotional (por seguridad, aunque lo siembras antes)
        $warehouse = ProductWarehouse::firstOrCreate(
            ['partner_id' => $partner->id, 'codigo' => '4promo-001'],
            ['name' => '4Promotional Almacén 001', 'nickname' => null, 'is_active' => 1]
        );

        // 3) Recorremos items (cada item es un color/sku)
        foreach ($products as $row) {
            // --- Normalización de valores ---
            $idArticulo   = trim((string)($row['id_articulo'] ?? ''));
            if ($idArticulo === '') {
                $this->command?->warn('Saltando registro sin id_articulo.');
                continue;
            }

            $slugProducto = Str::slug($idArticulo);
            $nombre       = $row['nombre_articulo'] ?? $idArticulo;
            $descripcion  = $row['descripcion'] ?? null;
            $precio       = is_numeric($row['precio'] ?? null) ? (float)$row['precio'] : 0.0;

            $categoria    = trim((string)($row['categoria'] ?? ''));
            $subcategoria = trim((string)($row['sub_categoria'] ?? ''));

            $altoCaja     = $this->numOrNull($row['alto_caja'] ?? null);
            $anchoCaja    = $this->numOrNull($row['ancho_caja'] ?? null);
            $largoCaja    = $this->numOrNull($row['largo_caja'] ?? null);
            $boxSize      = $this->joinDims([$altoCaja, $anchoCaja, $largoCaja]);

            $pesoCaja     = $this->strOrNull($row['peso_caja'] ?? null);
            $pzCaja       = $this->numOrNull($row['piezas_caja'] ?? null);

            $altoProd     = $this->numOrNull($row['medida_producto_alto'] ?? null);
            $anchoProd    = $this->numOrNull($row['medida_producto_ancho'] ?? null);
            $profProd     = $this->numOrNull($row['profundidad_articulo'] ?? null);
            $productSize  = $this->joinDims([$altoProd, $anchoProd, $profProd]);

            $areaImp      = $this->strOrNull($row['area_impresion'] ?? null);

            $isFeatured   = strtoupper((string)($row['producto_promocion'] ?? 'NO')) === 'SI';
            $isNew        = strtoupper((string)($row['producto_nuevo'] ?? 'NO')) === 'SI';

            $metodosRaw   = $this->strOrNull($row['metodos_impresion'] ?? null);
            $tecnicas     = $this->parseTechniques($metodosRaw); // array<string>
            $keywords     = $metodosRaw;

            // 3.1) Categoría
            $category = ProductCategory::firstOrCreate(
            ['slug' => Str::slug($categoria ?: 'sin-categoria'), 'partner_id' => $partner->id],
            ['name' => $categoria ?: 'Sin categoría', 'subcategory' => $subcategoria ?: null, 'is_active' => true]
        );

            // 3.2) Imagen principal del producto (uso tipo 'imagen' como 1a opción)
            $mainImageUrl = $this->firstImageUrl($row['imagenes'] ?? [], ['imagen', 'imagen_extra', 'imagen_color']);
            $mainImage    = $this->downloadImageOrNull($mainImageUrl, "products/4promotional/{$slugProducto}.jpg");

            // 3.3) Producto (idempotente por slug)
            $product = Product::updateOrCreate(
                ['slug' => $slugProducto, 'partner_id' => $partner->id],
                [
                    // Identificación
                    'owner_id'           => $owner->id,
                    'model_code'         => $idArticulo,
                    'name'               => $nombre,

                    // Comerciales
                    'price'              => $precio,
                    'description'        => $descripcion,
                    'keywords'           => $keywords,
                    'short_description'  => null,

                    // Atributos físicos
                    'material'           => null,
                    'packing_type'       => null,
                    'unit_package'       => $pzCaja,
                    'box_size'           => $boxSize,
                    'box_weight'         => $pesoCaja,
                    'product_weight'     => null,
                    'product_size'       => $productSize,
                    'area_print'         => $areaImp,

                    // Meta
                    'meta_description'   => $descripcion,
                    'meta_keywords'      => $nombre,
                    'featured'           => $isFeatured,
                    'new'                => $isNew,
                    'catalog_page'       => null,

                    // Imágenes
                    'main_image'         => $mainImage,

                    // Relaciones
                    'product_category_id'=> $category->id,
                    'partner_id'         => $partner->id,  // proveedor real
                    'owner_id'           => $owner->id,    // dueño/publicador (Printec)
                    'created_by'         => 1,
                    'is_active'          => true,
                    'is_own_product'     => false, // Es producto de proveedor
                    'is_public'          => true,  // Visible para todos
                ]
            );

            // 3.4) Técnicas de impresión (resync)
            DB::table('product_impression_technique')->where('product_id', $product->id)->delete();
            foreach (array_unique($tecnicas) as $tech) {
                if ($tech !== null && $tech !== '') {
                    DB::table('product_impression_technique')->insert([
                        'product_id' => $product->id,
                        'code'       => null,
                        'name'       => $tech,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 3.5) Variante (sku por color)
            $color     = trim((string)($row['color'] ?? 'UNICO'));
            $sku       = $idArticulo . '-' . $color;
            $slugSku   = Str::slug($sku);

            // Imagen de la variante: preferimos 'imagen_extra', luego 'imagen', luego 'imagen_color'
            $variantUrl  = $this->firstImageUrl($row['imagenes'] ?? [], ['imagen_extra','imagen','imagen_color']);
            $variantPath = $this->downloadImageOrNull($variantUrl, "products/4promotional/{$slugSku}.jpg") ?? $mainImage;

            $variant = ProductVariant::updateOrCreate(
                ['sku' => $sku],
                [
                    'product_id' => $product->id,
                    'slug'       => $slugSku,
                    'code_name'  => $sku,
                    'color_name' => $color,
                    'image'      => $variantPath,
                ]
            );

            // 3.6) Stock de la variante (único almacén 4promo-001)
            $stock = is_numeric($row['inventario'] ?? null) ? (int)$row['inventario'] : 0;
            \App\Models\ProductStock::updateOrCreate(
                [
                    'variant_id'   => $variant->id,
                    'warehouse_id' => $warehouse->id,
                ],
                ['stock' => max(0, $stock)]
            );
        }

        $this->command?->info('4Promotional: productos, variantes, técnicas y stock sincronizados.');
    }

    // ----------------- Helpers -----------------

    private function numOrNull($v): ?float
    {
        if ($v === null) return null;
        if (is_string($v) && strtolower(trim($v)) === 'null') return null;
        return is_numeric($v) ? (float)$v : null;
    }

    private function strOrNull($v): ?string
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        if ($s === '' || strtolower($s) === 'null') return null;
        return $s;
    }

    private function joinDims(array $dims): ?string
    {
        // arma "A x B x C" sólo con valores presentes y > 0
        $clean = array_values(array_filter($dims, fn($n) => is_numeric($n) && (float)$n > 0));
        if (count($clean) === 0) return null;
        return implode(' x ', array_map(function ($n) {
            // evita decimales .0 feos
            return (fmod($n, 1.0) === 0.0) ? (string)intval($n) : (string)$n;
        }, $clean));
    }

    private function parseTechniques(?string $raw): array
    {
        if (!$raw) return [];
        // separadores: '-' y ','
        $parts = preg_split('/[-,]/', $raw);
        return array_values(array_filter(array_map(function ($p) {
            $t = trim($p);
            // normaliza capitalización
            return $t !== '' ? $t : null;
        }, $parts)));
    }

    private function firstImageUrl(array $imagenes, array $prefer): ?string
    {
        // Busca en el arreglo de imágenes por prioridad de tipo
        foreach ($prefer as $tipo) {
            foreach ($imagenes as $im) {
                if (($im['tipo_imagen'] ?? null) === $tipo) {
                    return $this->encodeSpaces($im['url_imagen'] ?? null);
                }
            }
        }
        // si no se encontró por tipo, intenta la primera
        if (!empty($imagenes[0]['url_imagen'])) {
            return $this->encodeSpaces($imagenes[0]['url_imagen']);
        }
        return null;
    }

    private function encodeSpaces(?string $url): ?string
    {
        if (!$url) return null;
        // sólo reemplazo espacios por %20, sin romper la URL
        return str_replace(' ', '%20', $url);
    }

    private function downloadImageOrNull(?string $url, string $saveAs): ?string
    {
        if (!$url) return null;
        try {
            $resp = Http::withOptions(['verify' => false])
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url);

            if ($resp->successful()) {
                Storage::disk('public')->put($saveAs, $resp->body());
                return $saveAs; // guardar path relativo al disk public
            } else {
                $this->command?->warn("No se pudo descargar imagen (status {$resp->status()}): $url");
            }
        } catch (\Throwable $e) {
            $this->command?->warn("Error al descargar imagen: " . $e->getMessage());
        }
        return null;
    }
}
