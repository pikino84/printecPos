<?php

namespace Tests\Feature\Partner;

use App\Mail\ProfileDeadlineAnnouncementMail;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AnnounceProfileDeadlineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Carbon::setTestNow('2026-05-10');
    }

    public function test_envia_a_asociado_con_perfil_incompleto_y_marca_timestamp(): void
    {
        $partner = Partner::factory()->asociado()->create([
            'profile_deadline_at' => '2026-05-23',
            'contact_email' => 'asociado@printec.test',
        ]);

        $this->artisan('partners:announce-profile-deadline')->assertSuccessful();

        Mail::assertQueued(ProfileDeadlineAnnouncementMail::class, fn ($mail) => $mail->partner->is($partner));
        $this->assertNotNull($partner->fresh()->deadline_announcement_sent_at);
    }

    public function test_idempotente_no_reenvia_si_ya_se_envio(): void
    {
        Partner::factory()->asociado()->create([
            'profile_deadline_at' => '2026-05-23',
            'contact_email' => 'asociado@printec.test',
            'deadline_announcement_sent_at' => '2026-05-10 09:00:00',
        ]);

        $this->artisan('partners:announce-profile-deadline')->assertSuccessful();

        Mail::assertNotQueued(ProfileDeadlineAnnouncementMail::class);
    }

    public function test_no_envia_a_mixto_ni_proveedor(): void
    {
        Partner::factory()->mixto()->create([
            'profile_deadline_at' => '2026-05-23',
            'contact_email' => 'mixto@printec.test',
        ]);
        Partner::factory()->proveedor()->create([
            'profile_deadline_at' => '2026-05-23',
            'contact_email' => 'proveedor@printec.test',
        ]);

        $this->artisan('partners:announce-profile-deadline')->assertSuccessful();

        Mail::assertNotQueued(ProfileDeadlineAnnouncementMail::class);
    }

    public function test_no_envia_a_partner_vetado(): void
    {
        Partner::factory()->asociado()->create([
            'profile_deadline_at' => '2026-05-23',
            'contact_email' => 'vetado@printec.test',
            'vetoed_until' => '2027-01-01',
        ]);

        $this->artisan('partners:announce-profile-deadline')->assertSuccessful();

        Mail::assertNotQueued(ProfileDeadlineAnnouncementMail::class);
    }

    public function test_no_envia_a_partner_sin_email(): void
    {
        Partner::factory()->asociado()->create([
            'profile_deadline_at' => '2026-05-23',
            'contact_email' => null,
        ]);

        $this->artisan('partners:announce-profile-deadline')->assertSuccessful();

        Mail::assertNotQueued(ProfileDeadlineAnnouncementMail::class);
    }

    public function test_dry_run_no_envia_correos(): void
    {
        $partner = Partner::factory()->asociado()->create([
            'profile_deadline_at' => '2026-05-23',
            'contact_email' => 'asociado@printec.test',
        ]);

        $this->artisan('partners:announce-profile-deadline', ['--dry-run' => true])->assertSuccessful();

        Mail::assertNotQueued(ProfileDeadlineAnnouncementMail::class);
        $this->assertNull($partner->fresh()->deadline_announcement_sent_at);
    }

    public function test_limit_respetado(): void
    {
        Partner::factory()->asociado()->count(3)->create([
            'profile_deadline_at' => '2026-05-23',
        ]);

        $this->artisan('partners:announce-profile-deadline', ['--limit' => 2])->assertSuccessful();

        Mail::assertQueuedCount(2);
    }
}
