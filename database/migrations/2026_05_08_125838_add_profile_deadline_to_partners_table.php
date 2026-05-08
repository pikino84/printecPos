<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            // Plazo para completar perfil (created_at + 15 días). null = no aplica todavía o es Printec.
            $table->date('profile_deadline_at')->nullable()->after('comments');

            // Si > now(), el partner está vetado (no puede loguearse). Se setea cuando vence el plazo.
            $table->date('vetoed_until')->nullable()->after('profile_deadline_at');

            // Marca de qué recordatorios ya se enviaron (evita duplicados al correr el job diariamente).
            $table->date('reminder_7d_sent_at')->nullable()->after('vetoed_until');
            $table->date('reminder_3d_sent_at')->nullable()->after('reminder_7d_sent_at');

            $table->index(['vetoed_until']);
            $table->index(['profile_deadline_at']);
        });

        // Backfill: el deadline normal es created_at + 15 días. Para partners ya existentes
        // (>15 días de antigüedad cuando se introduce la feature), eso dejaría el deadline
        // en el pasado y el primer cron los vetaría a todos. Solución: damos a los existentes
        // un plazo nuevo desde HOY + 15 días para que tengan oportunidad de completar perfil.
        DB::statement("
            UPDATE partners
            SET profile_deadline_at = GREATEST(
                DATE_ADD(DATE(created_at), INTERVAL 15 DAY),
                DATE_ADD(CURDATE(), INTERVAL 15 DAY)
            )
            WHERE profile_deadline_at IS NULL
              AND type IN ('Asociado', 'Mixto')
        ");
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropIndex(['vetoed_until']);
            $table->dropIndex(['profile_deadline_at']);
            $table->dropColumn([
                'profile_deadline_at',
                'vetoed_until',
                'reminder_7d_sent_at',
                'reminder_3d_sent_at',
            ]);
        });
    }
};
