<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuoteItem>
 */
class QuoteItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 20);
        $unitPrice = fake()->randomFloat(2, 50, 1000);

        return [
            'quote_id' => Quote::factory(),
            'variant_id' => ProductVariant::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'cost_price' => $unitPrice * 0.7,
            'subtotal' => $quantity * $unitPrice,
        ];
    }
}
