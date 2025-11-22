<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PricingTier;
use App\Models\PricingSetting;
use Illuminate\Support\Str;

class PricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear settings globales
        $this->createSettings();
        
        // 2. Crear tiers de precio
        $this->createTiers();
    }

    private function createSettings()
    {
        $settings = [
            [
                'key' => 'printec_markup',
                'value' => '52.00',
                'type' => 'decimal',
                'label' => 'Markup de Printec (%)',
                'description' => 'Porcentaje de ganancia que Printec aplica sobre el precio base de proveedores',
                'group' => 'pricing',
                'is_editable' => true,
            ],
            [
                'key' => 'tax_rate',
                'value' => '16.00',
                'type' => 'decimal',
                'label' => 'Tasa de IVA (%)',
                'description' => 'Porcentaje de IVA aplicable a todas las ventas',
                'group' => 'tax',
                'is_editable' => true,
            ],
            [
                'key' => 'auto_tier_assignment',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Asignación Automática de Niveles',
                'description' => 'Asignar niveles automáticamente cada inicio de mes',
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
    }

    private function createTiers()
    {
        $tiers = [
            [
                'name' => 'Junior',
                'slug' => 'junior',
                'min_monthly_purchases' => 0,
                'max_monthly_purchases' => 4999.99,
                'discount_percentage' => 0,
                'description' => 'Nivel inicial para nuevos partners',
                'order' => 10,
            ],
            [
                'name' => 'Distribuidor',
                'slug' => 'distribuidor',
                'min_monthly_purchases' => 5000.00,
                'max_monthly_purchases' => 20000.00,
                'discount_percentage' => 0,
                'description' => 'Precio especial de distribuidor',
                'order' => 20,
            ],
            [
                'name' => 'Básico A',
                'slug' => 'basico-a',
                'min_monthly_purchases' => 20001.00,
                'max_monthly_purchases' => 50000.00,
                'discount_percentage' => 2,
                'description' => 'Nivel Básico A con 2% de descuento',
                'order' => 30,
            ],
            [
                'name' => 'Básico B',
                'slug' => 'basico-b',
                'min_monthly_purchases' => 50001.00,
                'max_monthly_purchases' => 100000.00,
                'discount_percentage' => 4,
                'description' => 'Nivel Básico B con 4% de descuento',
                'order' => 40,
            ],
            [
                'name' => 'Bronce A',
                'slug' => 'bronce-a',
                'min_monthly_purchases' => 100001.00,
                'max_monthly_purchases' => 200000.00,
                'discount_percentage' => 6,
                'description' => 'Nivel Bronce A con 6% de descuento',
                'order' => 50,
            ],
            [
                'name' => 'Bronce B',
                'slug' => 'bronce-b',
                'min_monthly_purchases' => 200001.00,
                'max_monthly_purchases' => 300000.00,
                'discount_percentage' => 8,
                'description' => 'Nivel Bronce B con 8% de descuento',
                'order' => 60,
            ],
            [
                'name' => 'Bronce C',
                'slug' => 'bronce-c',
                'min_monthly_purchases' => 300001.00,
                'max_monthly_purchases' => 400000.00,
                'discount_percentage' => 10,
                'description' => 'Nivel Bronce C con 10% de descuento',
                'order' => 70,
            ],
            [
                'name' => 'Oro A',
                'slug' => 'oro-a',
                'min_monthly_purchases' => 400001.00,
                'max_monthly_purchases' => 600000.00,
                'discount_percentage' => 12,
                'description' => 'Nivel Oro A con 12% de descuento',
                'order' => 80,
            ],
        ];

        foreach ($tiers as $tier) {
            PricingTier::updateOrCreate(
                ['slug' => $tier['slug']],
                $tier
            );
        }
    }
}