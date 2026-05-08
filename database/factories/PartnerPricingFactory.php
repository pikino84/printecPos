<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PricingTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartnerPricing>
 */
class PartnerPricingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'markup_percentage' => 0,
            'current_tier_id' => PricingTier::factory(),
            'last_month_purchases' => 0,
            'current_month_purchases' => 0,
            'manual_tier_override' => false,
        ];
    }
}
