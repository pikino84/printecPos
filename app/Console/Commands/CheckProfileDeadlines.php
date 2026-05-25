<?php

namespace App\Console\Commands;

use App\Mail\ProfileDeadlineExceededMail;
use App\Mail\ProfileReminderMail;
use App\Models\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Job diario que enforce la regla de "completa tu perfil en 15 días".
 * Manda recordatorios al día 7 y al deadline-3, y vetea por 1 año si llega
 * al deadline con perfil < 100%.
 */
class CheckProfileDeadlines extends Command
{
    protected $signature = 'partners:check-profile-deadlines';

    protected $description = 'Recordatorios y veto por perfil incompleto (épica 05)';

    public function handle(): int
    {
        $today = now()->startOfDay();

        // Solo Asociados puros entran al flujo de recordatorio/veto.
        // Mixto y Proveedor son socios estratégicos exentos de la regla de los 15 días.
        $partners = Partner::query()
            ->where('type', 'Asociado')
            ->whereNotNull('profile_deadline_at')
            ->whereNull('vetoed_until')
            ->get();

        $reminded7 = 0;
        $reminded3 = 0;
        $vetoed = 0;

        // Respaldo: levantar el veto a quienes ya completaron el perfil (p. ej. cuando
        // Printec lo completó por ellos vía razón social, banco o branding). El método
        // es idempotente y valida el 100% internamente.
        $lifted = 0;
        $vetoedPartners = Partner::query()
            ->where('type', 'Asociado')
            ->whereNotNull('vetoed_until')
            ->get();

        foreach ($vetoedPartners as $partner) {
            if ($partner->liftVetoIfProfileComplete()) {
                $lifted++;
            }
        }

        foreach ($partners as $partner) {
            // Si ya tiene perfil completo, limpiar deadline y seguir.
            if ($partner->hasCompleteProfile()) {
                continue;
            }

            $daysRemaining = $partner->daysUntilDeadline();

            if ($daysRemaining === null) {
                continue;
            }

            // Veto al cumplirse el deadline.
            if ($daysRemaining <= 0) {
                $partner->update([
                    'vetoed_until' => $today->copy()->addYear(),
                    'is_active' => false,
                ]);
                $this->dispatchMail($partner, new ProfileDeadlineExceededMail($partner));
                $vetoed++;

                continue;
            }

            // Recordatorio "tienes 3 días" — solo si nunca se envió.
            if ($daysRemaining <= 3 && $partner->reminder_3d_sent_at === null) {
                $this->dispatchMail($partner, new ProfileReminderMail($partner, $daysRemaining));
                $partner->update(['reminder_3d_sent_at' => $today]);
                $reminded3++;

                continue;
            }

            // Recordatorio día 7 — solo si nunca se envió.
            // Calculamos "día 7" como `created_at + 7 días`.
            $reminderAt = $partner->created_at->copy()->startOfDay()->addDays(7);
            if ($today->equalTo($reminderAt) && $partner->reminder_7d_sent_at === null) {
                $this->dispatchMail($partner, new ProfileReminderMail($partner, $daysRemaining));
                $partner->update(['reminder_7d_sent_at' => $today]);
                $reminded7++;
            }
        }

        $this->info("Recordatorio 7d: {$reminded7} | Recordatorio 3d: {$reminded3} | Vetados: {$vetoed} | Veto levantado: {$lifted}");

        return self::SUCCESS;
    }

    private function dispatchMail(Partner $partner, $mailable): void
    {
        $email = $partner->contact_email;
        if (! $email) {
            return;
        }

        Mail::to($email)->queue($mailable);
    }
}
