<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $sku = strtoupper(Str::random(8));

        return [
            'product_id' => Product::factory(),
            'sku' => $sku,
            'slug' => Str::slug($sku).'-'.Str::random(4),
            'code_name' => fake()->bothify('VAR-####'),
            'color_name' => fake()->safeColorName(),
            'price' => fake()->randomFloat(2, 50, 5000),
        ];
    }
}
