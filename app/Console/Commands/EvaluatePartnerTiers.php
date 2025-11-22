<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Partner;
use App\Models\PartnerPricing;
use App\Models\PricingTier;
use App\Models\PartnerTierHistory;
use Carbon\Carbon;

class EvaluatePartnerTiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pricing:evaluate-tiers {--partner-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evalúa las compras mensuales de partners y asigna niveles de precio';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando evaluación de niveles de precio...');
        
        // Período del mes anterior
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        
        $this->info("Período: {$lastMonthStart->format('Y-m-d')} a {$lastMonthEnd->format('Y-m-d')}");
        
        // Si se especifica un partner, solo evaluar ese
        if ($partnerId = $this->option('partner-id')) {
            $partner = Partner::findOrFail($partnerId);
            $this->evaluatePartner($partner, $lastMonthStart, $lastMonthEnd);
            $this->info("Partner evaluado: {$partner->name}");
            return Command::SUCCESS;
        }
        
        // Evaluar todos los partners activos que pueden comprar
        $partners = Partner::where('is_active', true)
            ->whereIn('type', ['Asociado', 'Mixto'])
            ->get();
        
        $evaluated = 0;
        $skipped = 0;
        
        foreach ($partners as $partner) {
            $pricing = $partner->pricing;
            
            // Si tiene override manual, saltarlo
            if ($pricing && $pricing->manual_tier_override) {
                $this->warn("Saltando {$partner->name} (override manual)");
                $skipped++;
                continue;
            }
            
            $this->evaluatePartner($partner, $lastMonthStart, $lastMonthEnd);
            $evaluated++;
        }
        
        $this->info("\n✓ Evaluación completada:");
        $this->info("  - Partners evaluados: {$evaluated}");
        $this->info("  - Partners saltados: {$skipped}");
        
        return Command::SUCCESS;
    }
    
    /**
     * Evaluar un partner específico
     */
    private function evaluatePartner(Partner $partner, Carbon $periodStart, Carbon $periodEnd)
    {
        // Obtener o crear pricing config
        $pricing = $partner->getPricingConfig();
        
        // Mover compras del mes actual al mes pasado
        $purchasesAmount = $pricing->current_month_purchases;
        $pricing->last_month_purchases = $purchasesAmount;
        $pricing->current_month_purchases = 0;
        
        // Determinar tier apropiado
        $tier = PricingTier::getTierForAmount($purchasesAmount);
        
        if ($tier) {
            $oldTierId = $pricing->current_tier_id;
            $pricing->current_tier_id = $tier->id;
            $pricing->tier_assigned_at = Carbon::now();
            $pricing->save();
            
            // Guardar en historial
            PartnerTierHistory::recordTierAssignment(
                $partner->id,
                $tier->id,
                $purchasesAmount,
                $periodStart,
                $periodEnd,
                false // no es manual
            );
            
            $changeInfo = $oldTierId ? " (cambió de tier)" : " (nuevo tier)";
            $this->line("  {$partner->name}: \${$purchasesAmount} → {$tier->name}{$changeInfo}");
        } else {
            $this->warn("  {$partner->name}: \${$purchasesAmount} → Sin tier asignado");
        }
    }
}