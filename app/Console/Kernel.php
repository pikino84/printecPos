<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\SyncInnovationProducts::class,
        \App\Console\Commands\SyncInnovationStock::class,
        \App\Console\Commands\SyncDobleVelaProducts::class,
        \App\Console\Commands\EvaluatePartnerTiers::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // ═══════════════════════════════════════════════════════
        // SINCRONIZACIÓN DOBLE VELA
        // API GetExistenciaAll disponible SOLO de 8PM a 8AM CDMX
        // Se ejecuta 3 veces en horario nocturno
        // ═══════════════════════════════════════════════════════

        // Sync 1: 20:30 CDMX (primera oportunidad después de apertura)
        $schedule->command('sync:doblevela-products')
            ->dailyAt('20:30')
            ->timezone('America/Mexico_City')
            ->runInBackground()
            ->withoutOverlapping(30)
            ->onSuccess(function () {
                Log::info('Sync Doble Vela 20:30 CDMX exitoso');
            })
            ->onFailure(function () {
                Log::error('Sync Doble Vela 20:30 CDMX falló');
            });

        // Sync 2: 00:30 CDMX (medianoche, captura cambios de fin de día)
        $schedule->command('sync:doblevela-products')
            ->dailyAt('00:30')
            ->timezone('America/Mexico_City')
            ->runInBackground()
            ->withoutOverlapping(30)
            ->onSuccess(function () {
                Log::info('Sync Doble Vela 00:30 CDMX exitoso');
            })
            ->onFailure(function () {
                Log::error('Sync Doble Vela 00:30 CDMX falló');
            });

        // Sync 3: 05:30 CDMX (antes de apertura, datos frescos para el día)
        $schedule->command('sync:doblevela-products')
            ->dailyAt('05:30')
            ->timezone('America/Mexico_City')
            ->runInBackground()
            ->withoutOverlapping(30)
            ->onSuccess(function () {
                Log::info('Sync Doble Vela 05:30 CDMX exitoso');
            })
            ->onFailure(function () {
                Log::error('Sync Doble Vela 05:30 CDMX falló');
            });

        // ═══════════════════════════════════════════════════════════
        // EVALUACIÓN DE NIVELES DE PRECIO (PRICING TIERS)
        // Se ejecuta el día 1 de cada mes a las 00:05 (CDMX)
        // Evalúa las compras del mes anterior y asigna niveles
        // ═══════════════════════════════════════════════════════════
        $schedule->command('pricing:evaluate-tiers')
            ->monthlyOn(1, '00:05')
            ->timezone('America/Mexico_City')
            ->runInBackground()
            ->withoutOverlapping(60)
            ->onSuccess(function () {
                Log::info('✅ Evaluación mensual de niveles de precio exitosa');
            })
            ->onFailure(function () {
                Log::error('❌ Evaluación mensual de niveles de precio falló');
            });
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}