<?php

namespace Database\Factories;

use App\Models\PartnerEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartnerEntityBankAccount>
 */
class PartnerEntityBankAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'partner_entity_id' => PartnerEntity::factory(),
            'bank_name' => fake()->randomElement(['BBVA', 'Santander', 'Banorte', 'Banamex', 'HSBC']),
            'account_holder' => fake()->name(),
            'clabe' => (string) fake()->numerify('##################'),
            'currency' => 'MXN',
            'is_active' => true,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function bankIncomplete(): static
    {
        return $this->state(fn () => ['clabe' => null]);
    }
}
