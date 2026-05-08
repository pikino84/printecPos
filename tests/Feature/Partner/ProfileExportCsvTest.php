<?php

namespace Tests\Feature\Partner;

use App\Models\Partner;
use App\Models\PartnerEntity;
use App\Models\PartnerEntityBankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileExportCsvTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Bypass del Gate (authorize en controller); el rol se setea por user.
        Gate::before(fn () => true);
        Role::findOrCreate('super admin', 'web');
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super admin');

        return $user;
    }

    public function test_export_devuelve_csv_con_partners_asociados_y_filas_esperadas(): void
    {
        $partnerIncompleto = Partner::factory()->asociado()->create([
            'name' => 'Asociado Incompleto SA',
            'contact_email' => 'inc@test.test',
        ]);
        $partnerCompleto = $this->buildCompletePartner('Asociado Completo SA');
        Partner::factory()->proveedor()->create(['name' => 'Proveedor X']); // No debe aparecer

        $response = $this->actingAs($this->adminUser())->get(route('partners.profile-progress.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $body = $response->streamedContent();

        $this->assertStringContainsString('ID,Nombre,Tipo,Contacto,Correo', $body);
        $this->assertStringContainsString('Asociado Incompleto SA', $body);
        $this->assertStringContainsString('Asociado Completo SA', $body);
        $this->assertStringNotContainsString('Proveedor X', $body, 'No debe incluir Proveedores');
    }

    public function test_filtro_profile_max_limita_resultados(): void
    {
        Partner::factory()->asociado()->create(['name' => 'Cero SA', 'logo' => null]);
        $this->buildCompletePartner('Cien SA');

        $response = $this->actingAs($this->adminUser())
            ->get(route('partners.profile-progress.export', ['profile_max' => 50]));

        $body = $response->streamedContent();

        $this->assertStringContainsString('Cero SA', $body);
        $this->assertStringNotContainsString('Cien SA', $body);
    }

    private function buildCompletePartner(string $name): Partner
    {
        $partner = Partner::factory()->asociado()->create([
            'name' => $name,
            'contact_name' => 'Frank',
            'contact_phone' => '5555555555',
            'contact_email' => 'frank@test.test',
            'direccion' => 'Av. 1',
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
            'account_holder' => 'Test',
            'clabe' => '012180001234567890',
        ]);

        return $partner;
    }
}
