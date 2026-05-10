<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sólo Asociado puro entra al flujo de deadline / recordatorio / veto (épica 05).
     * El backfill original de la migration 2026_05_08_125838 seteó deadline también
     * para Mixto. Esta migration limpia esos campos para Mixto y Proveedor para que
     * no queden datos colgados visibles en exports / panel super admin.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE partners
            SET profile_deadline_at = NULL,
                vetoed_until = NULL,
                reminder_7d_sent_at = NULL,
                reminder_3d_sent_at = NULL
            WHERE type IN ('Mixto', 'Proveedor')
        ");
    }

    public function down(): void
    {
        // No-op: no tiene sentido restaurar deadlines arbitrarios para Mixto/Proveedor.
    }
};
