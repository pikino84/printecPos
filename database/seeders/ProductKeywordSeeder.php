<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductKeywordSeeder extends Seeder
{
    public function run()
    {
        // Diccionario simple de sinónimos
        $synonyms = [
            'gorra' => ['cachucha', 'sombrero'],
            'playera' => ['camiseta', 'polo'],
            'taza' => ['jarro', 'vaso'],
            'pluma' => ['bolígrafo', 'lapicero'],
            'mochila' => ['backpack', 'morral'],
            'libreta' => ['cuaderno', 'notebook'],
            'termo' => ['botella', 'cantimplora'],
            'camisa' => ['blusa', 'polo'],
            // Agrega más aquí
        ];

        // Procesar todos los productos
        $products = Product::all();

        foreach ($products as $product) {
            $description = strtolower($product->description);
            $words = array_unique(str_word_count($description, 1)); // palabras únicas

            $keywords = [];

            foreach ($words as $word) {
                $keywords[] = $word;

                // Plural simple
                if (!Str::endsWith($word, 's')) {
                    $keywords[] = $word . 's'; // playera -> playeras
                }

                // Agregar sinónimos si existen
                if (array_key_exists($word, $synonyms)) {
                    $keywords = array_merge($keywords, $synonyms[$word]);
                }
            }

            $keywords = array_unique($keywords); // Eliminar duplicados
            $keywordsString = implode(', ', $keywords);

            $product->keywords = $keywordsString;
            $product->save();
        }
    }
}
