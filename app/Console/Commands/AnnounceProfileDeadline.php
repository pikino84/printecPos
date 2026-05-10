<?php

namespace App\Console\Commands;

use App\Mail\ProfileDeadlineAnnouncementMail;
use App\Models\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Anuncia la nueva política de "completa tu perfil en 15 días" a Asociados puros
 * con perfil < 100%. Idempotente: solo manda a quienes nunca recibieron este anuncio.
 *
 * Útil una sola vez al lanzar la feature. Los recordatorios continuos los manda
 * `partners:check-profile-deadlines` (día 7 desde registro y deadline-3).
 */
class AnnounceProfileDeadline extends Command
{
    protected $signature = 'partners:announce-profile-deadline
        {--dry-run : Solo lista a quién se enviaría, sin mandar correos}
        {--limit= : Máximo número de correos a enviar en esta corrida}';

    protected $description = 'Anuncia la política de plazo de perfil a Asociados con perfil incompleto (épica 05)';

    public function handle(): int
    {
        $today = now();

        $query = Partner::query()
            ->where('type', 'Asociado')
            ->whereNotNull('profile_deadline_at')
            ->whereNull('vetoed_until')
            ->whereNull('deadline_announcement_sent_at')
            ->whereNotNull('contact_email');

        $candidates = $query->get()->filter(fn (Partner $p) => ! $p->hasCompleteProfile());

        if ($candidates->isEmpty()) {
            $this->info('No hay Asociados pendientes de anuncio.');

            return self::SUCCESS;
        }

        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        if ($limit !== null) {
            $candidates = $candidates->take($limit);
        }

        $this->info("Candidatos: {$candidates->count()}");

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Nombre', 'Email', '%', 'Días restantes'],
                $candidates->map(fn (Partner $p) => [
                    $p->id,
                    $p->name,
                    $p->contact_email,
                    $p->profileCompletionPercentage(),
                    $p->daysUntilDeadline(),
                ])->all(),
            );
            $this->warn('Dry-run: no se enviaron correos.');

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($candidates as $partner) {
            try {
                Mail::to($partner->contact_email)->queue(new ProfileDeadlineAnnouncementMail($partner));
                $partner->update(['deadline_announcement_sent_at' => $today]);
                $sent++;
            } catch (\Throwable $e) {
                $this->error("Falló partner #{$partner->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Enviados: {$sent} | Fallidos: {$failed}");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
