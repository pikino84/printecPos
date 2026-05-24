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

class IncompleteProfileBlocksDiscountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // PricingSetting::get usa Cache::remember; sembramos el valor sin tocar la tabla
        // (que tiene 'label' NOT NULL y no nos interesa para esta prueba).
        Cache::put('pricing_setting_printec_markup', 50.0, now()->addHour());
    }

    public function test_asociado_perfil_incompleto_cobra_precio_publico(): void
    {
        $partner = Partner::factory()->asociado()->create();

        // Nivel público (de entrada): primero por orden, markup alto, sin descuento.
        PricingTier::factory()->create([
            'name' => 'Publico',
            'slug' => 'publico',
            'markup_percentage' => 54,
            'discount_percentage' => 0,
            'order' => 1,
        ]);

        // Nivel de distribuidor asignado al partner: mejor markup, orden posterior.
        $distribuidor = PricingTier::factory()->create([
            'name' => 'Distribuidor',
            'slug' => 'distribuidor',
            'markup_percentage' => 25,
            'discount_percentage' => 0,
            'order' => 2,
        ]);

        $pricing = PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
            'current_tier_id' => $distribuidor->id,
        ]);

        $this->assertTrue($pricing->shouldChargePublicPrice());

        // Pierde su nivel Distribuidor y cotiza al nivel Publico.
        // printec_markup=50 → ((100 * 1.50) * 1.54) = 231.00
        $this->assertSame(231.0, round($pricing->calculateCostPrice(100), 2));
    }

    public function test_perfil_incompleto_no_abarata_cuando_markup_global_es_cero(): void
    {
        // Regresión: con printec_markup=0 (modelo de markup por nivel), el perfil
        // incompleto debe cobrar el nivel público, NO el precio base sin aumento.
        Cache::put('pricing_setting_printec_markup', 0.0, now()->addHour());

        $partner = Partner::factory()->asociado()->create();
        PricingTier::factory()->create([
            'name' => 'Publico',
            'slug' => 'publico',
            'markup_percentage' => 54,
            'discount_percentage' => 0,
            'order' => 1,
        ]);
        $distribuidor = PricingTier::factory()->create([
            'name' => 'Distribuidor',
            'slug' => 'distribuidor',
            'markup_percentage' => 25,
            'discount_percentage' => 0,
            'order' => 2,
        ]);
        $pricing = PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
            'current_tier_id' => $distribuidor->id,
        ]);

        // Nivel público: 100 * 1.54 = 154 (no 100 base sin aumento).
        $this->assertSame(154.0, round($pricing->calculateCostPrice(100), 2));
    }

    public function test_asociado_perfil_completo_recibe_descuento(): void
    {
        $partner = $this->createCompletePartner();
        $tier = PricingTier::factory()->create([
            'markup_percentage' => 16,
            'discount_percentage' => 30,
        ]);
        $pricing = PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
            'current_tier_id' => $tier->id,
        ]);

        $this->assertFalse($pricing->shouldChargePublicPrice());

        // Con descuento: ((100 * 1.50) * 1.16) * 0.70 = 121.80
        $this->assertSame(121.80, round($pricing->calculateCostPrice(100), 2));
    }

    public function test_proveedor_perfil_incompleto_no_se_bloquea(): void
    {
        $partner = Partner::factory()->proveedor()->create();
        $pricing = PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
        ]);

        $this->assertFalse($pricing->shouldChargePublicPrice(),
            'Proveedores no están sujetos a la regla de perfil completo');
    }

    public function test_mixto_perfil_incompleto_no_se_bloquea(): void
    {
        $partner = Partner::factory()->create(['type' => 'Mixto']);
        $pricing = PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
        ]);

        $this->assertFalse($pricing->shouldChargePublicPrice(),
            'Mixto está exento del bloqueo de descuento por perfil incompleto');
    }

    public function test_breakdown_marca_profile_blocked_cuando_aplica(): void
    {
        $partner = Partner::factory()->asociado()->create();

        PricingTier::factory()->create([
            'name' => 'Publico',
            'slug' => 'publico',
            'markup_percentage' => 54,
            'discount_percentage' => 0,
            'order' => 1,
        ]);
        $distribuidor = PricingTier::factory()->create([
            'name' => 'Distribuidor',
            'slug' => 'distribuidor',
            'markup_percentage' => 25,
            'discount_percentage' => 0,
            'order' => 2,
        ]);

        PartnerPricing::factory()->create([
            'partner_id' => $partner->id,
            'current_tier_id' => $distribuidor->id,
        ]);

        $breakdown = $partner->getPricingConfig()->getPriceBreakdown(100);

        // Perfil bloqueado: el desglose usa el nivel público, no el de distribuidor.
        $this->assertTrue($breakdown['profile_blocked'] ?? false);
        $this->assertSame('Publico', $breakdown['tier_name']);
        $this->assertSame(0.0, (float) $breakdown['discount_percentage']);
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
