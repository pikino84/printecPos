<?php

namespace Tests\Feature\Partner;

use App\Models\Partner;
use App\Models\PartnerEntity;
use App\Models\PartnerEntityBankAccount;
use App\Models\PartnerPricing;
use App\Models\PricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WidgetPricingIndependentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // printec_markup global = 50% (sembrado sin tocar la tabla, igual que el test de perfil).
        Cache::put('pricing_setting_printec_markup', 50.0, now()->addHour());
    }

    public function test_widget_hereda_pricing_del_catalogo_cuando_no_esta_configurado(): void
    {
        $partner = $this->createCompletePartner();
        $catalogTier = PricingTier::factory()->create(['markup_percentage' => 25, 'discount_percentage' => 0]);

        $pricing = PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
            'markup_percentage' => 10,
            'current_tier_id' => $catalogTier->id,
            'api_markup_percentage' => null,
            'api_tier_id' => null,
        ]);

        // Sin pricing propio del widget, el precio del API == precio del catálogo.
        // (100 × 1.50 × 1.25) × 1.10 = 206.25
        $this->assertSame(206.25, round($pricing->calculateSalePrice(100), 2));
        $this->assertSame(206.25, round($pricing->calculateApiSalePrice(100), 2));
    }

    public function test_widget_usa_su_propio_markup_sin_afectar_catalogo(): void
    {
        $partner = $this->createCompletePartner();
        $catalogTier = PricingTier::factory()->create(['markup_percentage' => 25, 'discount_percentage' => 0]);

        $pricing = PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
            'markup_percentage' => 10,
            'current_tier_id' => $catalogTier->id,
            'api_markup_percentage' => 40,   // markup propio del widget
            'api_tier_id' => null,           // nivel heredado del catálogo
        ]);

        $cost = 100 * 1.50 * 1.25; // 187.5 (mismo nivel)
        $this->assertSame(206.25, round($pricing->calculateSalePrice(100), 2));   // catálogo intacto
        $this->assertSame(round($cost * 1.40, 2), round($pricing->calculateApiSalePrice(100), 2)); // 262.50
    }

    public function test_widget_usa_su_propio_nivel_sin_afectar_catalogo(): void
    {
        $partner = $this->createCompletePartner();
        $catalogTier = PricingTier::factory()->create(['markup_percentage' => 25, 'discount_percentage' => 0, 'order' => 2]);
        $apiTier = PricingTier::factory()->create(['markup_percentage' => 54, 'discount_percentage' => 0, 'order' => 1]);

        $pricing = PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
            'markup_percentage' => 10,
            'current_tier_id' => $catalogTier->id,
            'api_markup_percentage' => null, // markup heredado del catálogo (10%)
            'api_tier_id' => $apiTier->id,   // nivel propio del widget
        ]);

        $this->assertSame(206.25, round($pricing->calculateSalePrice(100), 2)); // catálogo: (100×1.5×1.25)×1.10
        // widget: (100 × 1.50 × 1.54) × 1.10 = 254.10
        $this->assertSame(254.10, round($pricing->calculateApiSalePrice(100), 2));
    }

    public function test_veto_perfil_incompleto_tambien_aplica_al_widget(): void
    {
        // Asociado con perfil incompleto: pierde su nivel tanto en POS como en widget.
        $partner = Partner::factory()->asociado()->create();
        PricingTier::factory()->create(['name' => 'Publico', 'slug' => 'publico', 'markup_percentage' => 54, 'discount_percentage' => 0, 'order' => 1]);
        $distribuidor = PricingTier::factory()->create(['name' => 'Distribuidor', 'slug' => 'distribuidor', 'markup_percentage' => 25, 'discount_percentage' => 0, 'order' => 2]);

        $pricing = PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
            'markup_percentage' => 10,
            'current_tier_id' => $distribuidor->id,
            'api_tier_id' => $distribuidor->id, // aun con nivel propio, el veto manda
            'api_markup_percentage' => null,
        ]);

        $this->assertTrue($pricing->shouldChargePublicPrice());
        // Cae al nivel público en ambos contextos: (100 × 1.50 × 1.54) × 1.10 = 254.10
        $this->assertSame(254.10, round($pricing->calculateSalePrice(100), 2));
        $this->assertSame(254.10, round($pricing->calculateApiSalePrice(100), 2));
    }

    private function createCompletePartner(): Partner
    {
        $partner = Partner::factory()->asociado()->create([
            'contact_name' => 'Frank',
            'contact_phone' => '5555555555',
            'contact_email' => 'frank@printec.mx',
            'direccion' => 'Av. Siempreviva 123',
            'logo' => 'logos/p.png',
        ]);
        $entity = PartnerEntity::factory()->create([
            'partner_id' => $partner->id,
            'rfc' => 'XAXX010101000',
            'razon_social' => 'Test SA',
            'telefono' => '5555555555',
            'direccion' => 'Fiscal 1',
        ]);
        PartnerEntityBankAccount::factory()->create([
            'partner_entity_id' => $entity->id,
            'bank_name' => 'BBVA',
            'account_holder' => 'Test SA',
            'clabe' => '012180001234567890',
        ]);

        return $partner->fresh();
    }
}
