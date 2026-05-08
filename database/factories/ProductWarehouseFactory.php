<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductWarehouse>
 */
class ProductWarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'codigo' => 'wh-'.Str::lower(Str::random(8)),
            'name' => fake()->city().' Warehouse',
            'nickname' => fake()->city(),
            'is_active' => true,
        ];
    }
}
