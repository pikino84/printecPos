<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'partner_id' => Partner::factory(),
            'quote_number' => 'COT-'.now()->year.'-'.Str::upper(Str::random(8)),
            'status' => 'draft',
            'source' => 'system',
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'is_urgent' => false,
            'urgency_fee' => 0,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['status' => 'accepted']);
    }

    public function withItems(int $count = 1): static
    {
        return $this->afterCreating(function (Quote $quote) use ($count) {
            \App\Models\QuoteItem::factory()
                ->count($count)
                ->for($quote)
                ->create();

            $quote->refresh()->calculateTotals();
        });
    }
}
