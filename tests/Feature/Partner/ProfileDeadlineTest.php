<?php

namespace Tests\Feature\Partner;

use App\Mail\ProfileDeadlineExceededMail;
use App\Mail\ProfileReminderMail;
use App\Models\Partner;
use App\Models\PartnerEntity;
use App\Models\PartnerEntityBankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProfileDeadlineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_recordatorio_3d_se_manda_y_se_marca_para_no_repetir(): void
    {
        Carbon::setTestNow('2026-05-08');

        $partner = Partner::factory()->asociado()->create([
            'created_at' => '2026-04-26', // hace 12 días
            'profile_deadline_at' => '2026-05-11', // 3 días para deadline
            'contact_email' => 'test@printec.test',
        ]);

        $this->artisan('partners:check-profile-deadlines')->assertSuccessful();

        Mail::assertQueued(ProfileReminderMail::class, fn ($mail) => $mail->daysRemaining === 3);
        $this->assertNotNull($partner->fresh()->reminder_3d_sent_at);

        // Segunda corrida: NO debe re-enviar
        Mail::fake();
        $this->artisan('partners:check-profile-deadlines')->assertSuccessful();
        Mail::assertNotQueued(ProfileReminderMail::class);
    }

    public function test_recordatorio_7d_se_manda_solo_el_dia_7(): void
    {
        Carbon::setTestNow('2026-05-08');

        $partner = Partner::factory()->asociado()->create([
            'created_at' => '2026-05-01', // hace exactamente 7 días
            'profile_deadline_at' => '2026-05-16',
            'contact_email' => 'test@printec.test',
        ]);

        $this->artisan('partners:check-profile-deadlines')->assertSuccessful();

        Mail::assertQueued(ProfileReminderMail::class);
        $this->assertNotNull($partner->fresh()->reminder_7d_sent_at);
    }

    public function test_veto_se_aplica_cuando_deadline_paso_con_perfil_incompleto(): void
    {
        Carbon::setTestNow('2026-05-08');

        $partner = Partner::factory()->asociado()->create([
            'created_at' => '2026-04-23',
            'profile_deadline_at' => '2026-05-08', // hoy es el deadline
            'contact_email' => 'test@printec.test',
        ]);

        $this->artisan('partners:check-profile-deadlines')->assertSuccessful();

        $partner->refresh();
        $this->assertTrue($partner->isVetoed());
        $this->assertSame('2027-05-08', $partner->vetoed_until->format('Y-m-d'));
        $this->assertFalse($partner->is_active);
        Mail::assertQueued(ProfileDeadlineExceededMail::class);
    }

    public function test_perfil_completo_no_se_vetea_aunque_pase_deadline(): void
    {
        Carbon::setTestNow('2026-05-08');

        $partner = Partner::factory()->asociado()->create([
            'contact_name' => 'Frank',
            'contact_phone' => '5555555555',
            'contact_email' => 'frank@printec.test',
            'direccion' => 'Av. 1',
            'logo' => 'logos/p.png',
            'created_at' => '2026-04-20',
            'profile_deadline_at' => '2026-05-05', // ya pasó
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

        $this->artisan('partners:check-profile-deadlines')->assertSuccessful();

        $this->assertFalse($partner->fresh()->isVetoed());
        Mail::assertNotQueued(ProfileDeadlineExceededMail::class);
    }

    public function test_proveedor_no_se_vetea(): void
    {
        Carbon::setTestNow('2026-05-08');

        $partner = Partner::factory()->proveedor()->create([
            'profile_deadline_at' => '2026-05-05', // ya pasó
            'contact_email' => 'p@printec.test',
        ]);

        $this->artisan('partners:check-profile-deadlines')->assertSuccessful();

        $this->assertFalse($partner->fresh()->isVetoed());
    }

    public function test_mixto_no_se_vetea_ni_recibe_recordatorios(): void
    {
        Carbon::setTestNow('2026-05-08');

        $partner = Partner::factory()->create([
            'type' => 'Mixto',
            'profile_deadline_at' => '2026-05-05', // ya pasó
            'contact_email' => 'm@printec.test',
        ]);

        $this->artisan('partners:check-profile-deadlines')->assertSuccessful();

        $this->assertFalse($partner->fresh()->isVetoed());
        Mail::assertNotQueued(ProfileDeadlineExceededMail::class);
        Mail::assertNotQueued(ProfileReminderMail::class);
    }

    public function test_login_bloqueado_si_partner_vetado(): void
    {
        $partner = Partner::factory()->asociado()->create([
            'vetoed_until' => now()->addMonths(6),
            'is_active' => false,
        ]);
        $user = User::factory()->forPartner($partner)->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_cron_levanta_veto_si_perfil_quedo_completo(): void
    {
        Carbon::setTestNow('2026-05-25');

        $partner = $this->completeProfilePartner([
            'vetoed_until' => '2027-05-25',
            'is_active' => false,
            'profile_deadline_at' => '2026-05-10',
        ]);
        $this->assertTrue($partner->fresh()->isVetoed());

        $this->artisan('partners:check-profile-deadlines')->assertSuccessful();

        $partner->refresh();
        $this->assertNull($partner->vetoed_until);
        $this->assertFalse($partner->isVetoed());
        $this->assertTrue($partner->is_active);
    }

    public function test_cron_no_levanta_veto_si_perfil_sigue_incompleto(): void
    {
        Carbon::setTestNow('2026-05-25');

        $partner = Partner::factory()->asociado()->create([
            'vetoed_until' => '2027-05-25',
            'is_active' => false,
        ]);

        $this->artisan('partners:check-profile-deadlines')->assertSuccessful();

        $partner->refresh();
        $this->assertTrue($partner->isVetoed());
        $this->assertFalse($partner->is_active);
    }

    public function test_lift_veto_no_hace_nada_si_no_estaba_vetado(): void
    {
        $partner = $this->completeProfilePartner(['vetoed_until' => null, 'is_active' => true]);

        $this->assertFalse($partner->liftVetoIfProfileComplete());
    }

    /**
     * Crea un Asociado con perfil 100% completo (fiscal + bancario + contacto + logo).
     */
    private function completeProfilePartner(array $overrides = []): Partner
    {
        $partner = Partner::factory()->asociado()->create(array_merge([
            'contact_name' => 'Frank',
            'contact_phone' => '5555555555',
            'contact_email' => 'frank@printec.test',
            'direccion' => 'Av. 1',
            'logo' => 'logos/p.png',
        ], $overrides));

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
