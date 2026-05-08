<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::random(6),
            'price' => fake()->randomFloat(2, 50, 5000),
            'partner_id' => Partner::factory(),
            // owner_id reusa el partner_id ya resuelto para evitar crear 2 Partners distintos
            'owner_id' => fn (array $attributes) => $attributes['partner_id'],
            'created_by' => User::factory(),
            'product_category_id' => ProductCategory::factory(),
            'is_active' => true,
            'is_own_product' => false,
            'is_public' => false,
        ];
    }

    public function perMeter(): static
    {
        return $this->state(fn () => ['unit_type' => Product::UNIT_TYPE_METRO_CUADRADO]);
    }

    public function ownProduct(): static
    {
        return $this->state(fn () => ['is_own_product' => true]);
    }

    public function public(): static
    {
        return $this->state(fn () => ['is_public' => true]);
    }
}
