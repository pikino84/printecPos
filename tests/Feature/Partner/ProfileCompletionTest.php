<?php

namespace Tests\Feature\Partner;

use App\Models\Partner;
use App\Models\PartnerEntity;
use App\Models\PartnerEntityBankAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_recien_creado_tiene_porcentaje_bajo(): void
    {
        $partner = Partner::factory()->create([
            'contact_name' => null,
            'contact_phone' => null,
            'contact_email' => null,
            'direccion' => null,
            'logo' => null,
        ]);

        $this->assertSame(0, $partner->profileCompletionPercentage());
        $this->assertFalse($partner->hasCompleteProfile());
    }

    public function test_solo_contacto_completo_da_20(): void
    {
        $partner = Partner::factory()->create([
            'contact_name' => 'Frank',
            'contact_phone' => '5555555555',
            'contact_email' => 'frank@printec.mx',
            'direccion' => 'Av. Siempreviva 123',
            'logo' => null,
        ]);

        $this->assertSame(20, $partner->profileCompletionPercentage());
    }

    public function test_solo_logo_da_10(): void
    {
        $partner = Partner::factory()->create([
            'contact_name' => null,
            'contact_phone' => null,
            'contact_email' => null,
            'direccion' => null,
            'logo' => 'logos/partner-1.png',
        ]);

        $this->assertSame(10, $partner->profileCompletionPercentage());
    }

    public function test_fiscal_incompleto_no_aporta(): void
    {
        $partner = Partner::factory()->create();
        PartnerEntity::factory()->fiscalIncomplete()->create([
            'partner_id' => $partner->id,
        ]);

        $missing = $partner->missingProfileFields();

        $this->assertContains('rfc', $missing['fiscal']);
        $this->assertContains('telefono', $missing['fiscal']);
    }

    public function test_fiscal_completo_da_40(): void
    {
        $partner = Partner::factory()->create([
            'contact_name' => null,
            'contact_phone' => null,
            'contact_email' => null,
            'direccion' => null,
            'logo' => null,
        ]);
        PartnerEntity::factory()->create([
            'partner_id' => $partner->id,
            'rfc' => 'XAXX010101000',
            'razon_social' => 'Test Razon SA de CV',
            'telefono' => '5555555555',
            'direccion' => 'Av. Fiscal 100',
        ]);

        $this->assertSame(40, $partner->profileCompletionPercentage());
    }

    public function test_bancario_completo_da_30(): void
    {
        $partner = Partner::factory()->create([
            'contact_name' => null,
            'contact_phone' => null,
            'contact_email' => null,
            'direccion' => null,
            'logo' => null,
        ]);
        $entity = PartnerEntity::factory()->fiscalIncomplete()->create([
            'partner_id' => $partner->id,
        ]);
        PartnerEntityBankAccount::factory()->create([
            'partner_entity_id' => $entity->id,
            'bank_name' => 'BBVA',
            'account_holder' => 'Test Razon SA de CV',
            'clabe' => '012180001234567890',
        ]);

        $this->assertSame(30, $partner->profileCompletionPercentage());
    }

    public function test_perfil_100_con_los_4_bloques(): void
    {
        $partner = Partner::factory()->create([
            'contact_name' => 'Frank',
            'contact_phone' => '5555555555',
            'contact_email' => 'frank@printec.mx',
            'direccion' => 'Av. Siempreviva 123',
            'logo' => 'logos/partner-1.png',
        ]);
        $entity = PartnerEntity::factory()->create([
            'partner_id' => $partner->id,
            'rfc' => 'XAXX010101000',
            'razon_social' => 'Test Razon SA de CV',
            'telefono' => '5555555555',
            'direccion' => 'Av. Fiscal 100',
        ]);
        PartnerEntityBankAccount::factory()->create([
            'partner_entity_id' => $entity->id,
            'bank_name' => 'BBVA',
            'account_holder' => 'Test Razon SA de CV',
            'clabe' => '012180001234567890',
        ]);

        $this->assertSame(100, $partner->profileCompletionPercentage());
        $this->assertTrue($partner->hasCompleteProfile());
        $this->assertEmpty($partner->missingProfileFields()['fiscal']);
        $this->assertEmpty($partner->missingProfileFields()['bank']);
        $this->assertEmpty($partner->missingProfileFields()['contact']);
        $this->assertEmpty($partner->missingProfileFields()['logo']);
    }

    public function test_cuenta_bancaria_inactiva_no_aporta(): void
    {
        $partner = Partner::factory()->create();
        $entity = PartnerEntity::factory()->fiscalIncomplete()->create([
            'partner_id' => $partner->id,
        ]);
        PartnerEntityBankAccount::factory()->create([
            'partner_entity_id' => $entity->id,
            'bank_name' => 'BBVA',
            'account_holder' => 'Test',
            'clabe' => '012180001234567890',
            'is_active' => false,
        ]);

        $this->assertContains('bank_name', $partner->missingProfileFields()['bank']);
    }

    public function test_missing_logo_aparece_en_bloque_logo(): void
    {
        $partner = Partner::factory()->create(['logo' => null]);

        $this->assertContains('logo', $partner->missingProfileFields()['logo']);
    }

    public function test_se_usa_default_entity_id_cuando_existe(): void
    {
        $partner = Partner::factory()->create();

        // Entity primera (id menor) — fiscal incompleta
        PartnerEntity::factory()->fiscalIncomplete()->create([
            'partner_id' => $partner->id,
        ]);

        // Entity default — fiscal completa
        $defaultEntity = PartnerEntity::factory()->create([
            'partner_id' => $partner->id,
            'rfc' => 'XAXX010101000',
            'razon_social' => 'Default Entity',
            'telefono' => '5555555555',
            'direccion' => 'Av. Default 1',
        ]);

        $partner->update(['default_entity_id' => $defaultEntity->id]);
        $partner->refresh();

        // Si toma la default, el bloque fiscal cuenta 40
        $this->assertSame(40, $partner->profileCompletionPercentage() & 40);
        $this->assertEmpty($partner->missingProfileFields()['fiscal']);
    }
}
