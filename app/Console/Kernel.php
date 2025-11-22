<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        // SINCRONIZACIÓN PRINCIPAL: 5:00 AM Cancún (4:00 AM CDMX)
        // ═══════════════════════════════════════════════════════
        $schedule->command('sync:doblevela-products')
            ->dailyAt('05:00')
            ->weekdays() // Lunes a viernes
            ->timezone('America/Cancun')
            ->runInBackground()
            ->withoutOverlapping(30)
            ->onSuccess(function () {
                Log::info('✅ Sync Doble Vela 5AM (Cancún) exitoso');
            })
            ->onFailure(function () {
                Log::error('❌ Sync Doble Vela 5AM (Cancún) falló');
            });
        
        // ═══════════════════════════════════════════════════════
        // SINCRONIZACIÓN NOCTURNA: 8:30 PM Cancún (7:30 PM CDMX)
        // Captura todos los cambios del día
        // ═══════════════════════════════════════════════════════
        $schedule->command('sync:doblevela-products')
            ->dailyAt('20:30')
            ->weekdays()
            ->timezone('America/Cancun')
            ->runInBackground()
            ->withoutOverlapping(30)
            ->onSuccess(function () {
                Log::info('✅ Sync Doble Vela 8:30PM (Cancún) exitoso');
            })
            ->onFailure(function () {
                Log::error('❌ Sync Doble Vela 8:30PM (Cancún) falló');
            });
        
        // ═══════════════════════════════════════════════════════
        // RETRY automático si falla la de 5AM
        // Se ejecuta a las 7:30 AM Cancún (6:30 AM CDMX)
        // ═══════════════════════════════════════════════════════
        $schedule->command('sync:doblevela-products')
            ->dailyAt('07:30')
            ->weekdays()
            ->timezone('America/Cancun')
            ->when(function () {
                // Solo ejecutar si la de 5AM falló
                $lastSync = \Illuminate\Support\Facades\Storage::get('doblevela_last_sync.txt') ?? '';
                return str_contains($lastSync, 'FAILED');
            })
            ->runInBackground()
            ->withoutOverlapping(30);

        // ═══════════════════════════════════════════════════════════
        // EVALUACIÓN DE NIVELES DE PRECIO (PRICING TIERS)
        // Se ejecuta el día 1 de cada mes a las 00:05 (CDMX)
        // Evalúa las compras del mes anterior y asigna niveles
        // ═══════════════════════════════════════════════════════════
        $schedule->command('pricing:evaluate-tiers')
            ->monthlyOn(1, '00:05')
            ->timezone('America/Mexico_City')
            ->runInBackground()
            ->withoutOverlapping(60) // Evita solapamiento por 60 minutos
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
