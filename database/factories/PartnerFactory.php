<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partner>
 */
class PartnerFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'contact_email' => fake()->unique()->safeEmail(),
            'direccion' => fake()->address(),
            'type' => 'Asociado',
            'is_active' => true,
            'api_show_prices' => true,
        ];
    }

    public function asociado(): static
    {
        return $this->state(fn () => ['type' => 'Asociado']);
    }

    public function proveedor(): static
    {
        return $this->state(fn () => ['type' => 'Proveedor']);
    }

    public function mixto(): static
    {
        return $this->state(fn () => ['type' => 'Mixto']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
