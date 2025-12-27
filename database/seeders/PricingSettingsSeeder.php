<?php

namespace Database\Seeders;

use App\Models\PricingSetting;
use Illuminate\Database\Seeder;

class PricingSettingsSeeder extends Seeder
{
    /**
     * Seed the pricing settings.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'printec_markup',
                'value' => '52',
                'type' => 'decimal',
                'label' => 'Markup Printec',
                'description' => 'Porcentaje de ganancia de Printec sobre precio base',
                'group' => 'pricing',
                'is_editable' => true,
            ],
            [
                'key' => 'tax_rate',
                'value' => '16',
                'type' => 'decimal',
                'label' => 'IVA',
                'description' => 'Porcentaje de impuesto',
                'group' => 'pricing',
                'is_editable' => true,
            ],
        ];

        foreach ($settings as $setting) {
            PricingSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Pricing settings seeded successfully!');
    }
}
