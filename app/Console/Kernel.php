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
        // Horarios disponibles API (CDMX): 
        //   09:00-10:00, 13:00-14:00, 17:00-18:00
        // ═══════════════════════════════════════════════════════
        
        // Ventana 1: 09:05 CDMX (10:05 Cancún)
        $schedule->command('sync:doblevela-products')
            ->dailyAt('09:05')
            ->weekdays()
            ->timezone('America/Mexico_City')
            ->runInBackground()
            ->withoutOverlapping(30)
            ->onSuccess(function () {
                Log::info('✅ Sync Doble Vela 09:05 CDMX exitoso');
            })
            ->onFailure(function () {
                Log::error('❌ Sync Doble Vela 09:05 CDMX falló');
            });
        
        // Ventana 2: 13:05 CDMX (14:05 Cancún)
        $schedule->command('sync:doblevela-products')
            ->dailyAt('13:05')
            ->weekdays()
            ->timezone('America/Mexico_City')
            ->runInBackground()
            ->withoutOverlapping(30)
            ->onSuccess(function () {
                Log::info('✅ Sync Doble Vela 13:05 CDMX exitoso');
            })
            ->onFailure(function () {
                Log::error('❌ Sync Doble Vela 13:05 CDMX falló');
            });
        
        // Ventana 3: 17:05 CDMX (18:05 Cancún)
        $schedule->command('sync:doblevela-products')
            ->dailyAt('17:05')
            ->weekdays()
            ->timezone('America/Mexico_City')
            ->runInBackground()
            ->withoutOverlapping(30)
            ->onSuccess(function () {
                Log::info('✅ Sync Doble Vela 17:05 CDMX exitoso');
            })
            ->onFailure(function () {
                Log::error('❌ Sync Doble Vela 17:05 CDMX falló');
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