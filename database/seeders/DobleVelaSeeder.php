<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Provider;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductStock;

class DobleVelaSeeder extends Seeder
{
    public function run(): void
    {
        $json = Storage::get('doblevela/products.json');
        $decoded = json_decode($json, true);
        $data = json_decode($decoded["GetExistenciaAllResult"], true)["Resultado"];

        $baseUrl = 'https://www.doblevela.com/images/large/';
        $localPath = 'products/doblevela/';
        $placeholder = 'placeholder.jpg';

        Storage::disk('public')->makeDirectory($localPath);

        $provider = Provider::firstOrCreate([
            'slug' => 'doble-vela',
        ], ['name' => 'Doble Vela']);

        foreach ($data as $item) {
            $category = Category::firstOrCreate([
                'slug' => Str::slug($item['Familia']),
            ], ['name' => $item['Familia']]);

            // Producto principal
            $product = Product::firstOrCreate([
                'slug' => Str::slug($item['MODELO']),
            ], [
                'sku' => $item['MODELO'],
                'name' => $item['NOMBRE'],
                'short_name' => $item['Nombre Corto'] ?? null,
                'description' => $item['Descripcion'] ?? null,
                'material' => $item['Material'] ?? null,
                'packing_type' => $item['Tipo Empaque'] ?? null,
                'impression_type' => $item['Tipo Impresion'] ?? null,
                'unit_package' => $item['Unidad Empaque'] ?? null,
                'box_size' => $item['Medida Caja Master'] ?? null,
                'box_weight' => $item['Peso caja'] ?? null,
                'product_weight' => $item['Peso Producto'] ?? null,
                'product_size' => $item['Medida Producto'] ?? null,
                'model_code' => $item['MODELO'],
                'category_id' => $category->id,
                'provider_id' => $provider->id,
            ]);

            // Imagen producto principal
            $sku = $item['MODELO'];
            $filename = $sku . '.jpg';
            
    $modelo = str_replace(' ', '', $sku);
    $color_img = explode("-", $item['COLOR']);
    $color_img = trim(end($color_img));
    $color_img = str_replace(' ', '', mb_strtolower($color_img, 'UTF-8'));
    $url = "{$baseUrl}{$modelo}_{$color_img}_lrg.jpg";
    
            $path = $localPath . $filename;

            try {
                // Registrar la URL generada
                \Log::info("Descargando imagen producto: " . $url);
            
                // Realizar la solicitud HTTP
                $response = Http::withOptions([
                    'verify' => false,
                ])->get($url);
            
                // Registrar el estado de la respuesta
                \Log::info("Estado producto ({$sku}): " . $response->status());
            
                // Verificar si la respuesta es exitosa
                if ($response->successful()) {
                    // Validar que el contenido sea una imagen
                    $contentType = $response->header('Content-Type');
                    if (Str::startsWith($contentType, 'image/')) {
                        // Guardar la imagen en el almacenamiento local
                        file_put_contents(storage_path("app/public/" . $path), $response->body());
                        $product->image_path = $path;
                    } else {
                        throw new \Exception("El contenido no es una imagen. Tipo: " . $contentType);
                    }
                } else {
                    throw new \Exception("La respuesta no fue exitosa. Código de estado: " . $response->status());
                }
            } catch (\Exception $e) {
                // Registrar el error en los logs
                \Log::error("Error descargando imagen producto ({$sku}): " . $e->getMessage());
            
                // Usar la imagen placeholder
                copy(storage_path("app/public/" . $placeholder), storage_path("app/public/" . $path));
                $product->image_path = $path;
            }

            $product->save();

            // Variantes
            $variant = ProductVariant::updateOrCreate([
                'sku' => $item['CLAVE'],
            ], [
                'product_id' => $product->id,
                'color' => $item['COLOR'],
                'color_code' => explode(' - ', $item['COLOR'])[0] ?? null,
                'stock_total' => $item['EXISTENCIAS'],
                'reserved' => $item['Apartado'],
                'price' => $item['Price'],
                'status' => $item['Status'],
                'arrival_qty_1' => $item['Por llegar 1'],
                'arrival_date_1' => $item['Fecha aprox de llegada 1'],
                'arrival_qty_2' => $item['Por llegar 2'],
                'arrival_date_2' => $item['Fecha aprox de llegada 2'],
            ]);

            // Imagen variante
            $vsku = $item['CLAVE'];
            $vfilename = $vsku . '.jpg';
            
    $vmodelo = str_replace(' ', '', $item['MODELO']);
    $vcolor_img = explode("-", $item['COLOR']);
    $vcolor_img = trim(end($vcolor_img));
    $vcolor_img = str_replace(' ', '', mb_strtolower($vcolor_img, 'UTF-8'));
    $vurl = "{$baseUrl}{$vmodelo}_{$vcolor_img}_lrg.jpg";
    
            $vpath = $localPath . $vfilename;

            try {
                
    \Log::info("Descargando imagen variante: " . $vurl);

    $vresponse = Http::withOptions([
        'verify' => false,
    ])->get($vurl);

    \Log::info("Estado variante ({$vsku}): " . $vresponse->status());
    
                if ($vresponse->successful()) {
                    file_put_contents(storage_path("app/public/" . $vpath), $vresponse->body());
                    $variant->image_path = $vpath;
                } else {
                    throw new \Exception();
                }
            } catch (\Exception) {
                copy(storage_path("app/public/" . $placeholder), storage_path("app/public/" . $vpath));
                $variant->image_path = $vpath;
            }

            $variant->save();

            foreach ([7, 8, 9, 10, 20, 24] as $warehouse) {
                ProductStock::updateOrCreate([
                    'variant_id' => $variant->id,
                    'warehouse_id' => $warehouse,
                ], [
                    'available' => $item['Disponible Almacen ' . $warehouse] ?? 0,
                    'reserved' => $item['Comprometido Almacen ' . $warehouse] ?? 0,
                ]);
            }

            // Imagen de categoría si no tiene
            if (!$category->image_path) {
                $category->image_path = $product->image_path;
                $category->save();
            }
        }
    }
}