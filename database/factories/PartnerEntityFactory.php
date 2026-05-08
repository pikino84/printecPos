<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartnerEntity>
 */
class PartnerEntityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'razon_social' => fake()->unique()->company().' '.Str::upper(Str::random(4)),
            'rfc' => Str::upper(Str::random(13)),
            'telefono' => fake()->phoneNumber(),
            'correo_contacto' => fake()->unique()->safeEmail(),
            'direccion' => fake()->address(),
            'is_active' => true,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function fiscalIncomplete(): static
    {
        return $this->state(fn () => [
            'rfc' => null,
            'telefono' => null,
        ]);
    }
}
