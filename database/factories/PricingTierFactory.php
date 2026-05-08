<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingTier>
 */
class PricingTierFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Tier '.Str::upper(Str::random(4));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'min_monthly_purchases' => 0,
            'max_monthly_purchases' => null,
            'markup_percentage' => 16,
            'discount_percentage' => 30,
            'order' => 1,
            'is_active' => true,
        ];
    }

    public function noDiscount(): static
    {
        return $this->state(fn () => ['discount_percentage' => 0]);
    }
}
