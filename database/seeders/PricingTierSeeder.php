<?php

namespace Database\Seeders;

use App\Models\PricingTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PricingTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Junior',
                'slug' => 'junior',
                'min_monthly_purchases' => 0,
                'max_monthly_purchases' => 4999.99,
                'markup_percentage' => 52.00,
                'discount_percentage' => 0.00,
                'description' => 'Nivel inicial para compras de $0 a $4,999. Markup del 52%.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Distribuidor',
                'slug' => 'distribuidor',
                'min_monthly_purchases' => 5000.00,
                'max_monthly_purchases' => 20000.00,
                'markup_percentage' => 22.00,
                'discount_percentage' => 0.00,
                'description' => 'Nivel distribuidor para compras de $5,000 a $20,000. Markup del 22%, sin descuento.',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Básico A',
                'slug' => 'basico-a',
                'min_monthly_purchases' => 20001.00,
                'max_monthly_purchases' => 50000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 2.00,
                'description' => 'Nivel Básico A para compras de $20,001 a $50,000. Markup del 16% con 2% de descuento.',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Básico B',
                'slug' => 'basico-b',
                'min_monthly_purchases' => 50001.00,
                'max_monthly_purchases' => 100000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 4.00,
                'description' => 'Nivel Básico B para compras de $50,001 a $100,000. Markup del 16% con 4% de descuento.',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Bronce A',
                'slug' => 'bronce-a',
                'min_monthly_purchases' => 100001.00,
                'max_monthly_purchases' => 200000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 6.00,
                'description' => 'Nivel Bronce A para compras de $100,001 a $200,000. Markup del 16% con 6% de descuento.',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Bronce B',
                'slug' => 'bronce-b',
                'min_monthly_purchases' => 200001.00,
                'max_monthly_purchases' => 300000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 8.00,
                'description' => 'Nivel Bronce B para compras de $200,001 a $300,000. Markup del 16% con 8% de descuento.',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Bronce C',
                'slug' => 'bronce-c',
                'min_monthly_purchases' => 300001.00,
                'max_monthly_purchases' => 400000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 10.00,
                'description' => 'Nivel Bronce C para compras de $300,001 a $400,000. Markup del 16% con 10% de descuento.',
                'order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Oro A',
                'slug' => 'oro-a',
                'min_monthly_purchases' => 400001.00,
                'max_monthly_purchases' => 600000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 12.00,
                'description' => 'Nivel Oro A para compras de $400,001 a $600,000. Markup del 16% con 12% de descuento.',
                'order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Oro B',
                'slug' => 'oro-b',
                'min_monthly_purchases' => 600001.00,
                'max_monthly_purchases' => 800000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 14.00,
                'description' => 'Nivel Oro B para compras de $600,001 a $800,000. Markup del 16% con 14% de descuento.',
                'order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Oro C',
                'slug' => 'oro-c',
                'min_monthly_purchases' => 800001.00,
                'max_monthly_purchases' => 1000000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 16.00,
                'description' => 'Nivel Oro C para compras mayores a $1,000,000. Markup del 16% con 16% de descuento.',
                'order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Platinum A',
                'slug' => 'platinum-a',
                'min_monthly_purchases' => 1000001.00,
                'max_monthly_purchases' => 1400000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 18.00,
                'description' => 'Nivel Platinum A para compras de $1,000,001 a $1,400,000. Markup del 16% con 18% de descuento.',
                'order' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Platinum B',
                'slug' => 'platinum-b',
                'min_monthly_purchases' => 1400001.00,
                'max_monthly_purchases' => 1600000.00,
                'markup_percentage' => 16.00,
                'discount_percentage' => 20.00,
                'description' => 'Nivel Platinum B para compras de $1,400,001 a $1,600,000. Markup del 16% con 20% de descuento.',
                'order' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tier) {
            PricingTier::updateOrCreate(
                ['slug' => $tier['slug']],
                $tier
            );
        }

        $this->command->info('✅ Se crearon/actualizaron ' . count($tiers) . ' niveles de precio.');
    }
}