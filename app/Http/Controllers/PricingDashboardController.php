<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerPricing;
use App\Models\PricingTier;
use App\Models\PartnerTierHistory;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PricingDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super admin|admin');
    }

    /**
     * Display the pricing dashboard
     */
    public function index()
    {
        // Estadísticas generales
        $stats = $this->getGeneralStats();
        
        // Distribución de partners por tier
        $tierDistribution = $this->getTierDistribution();
        
        // Top 10 partners por compras del mes
        $topPartners = $this->getTopPartners();
        
        // Evolución de compras (últimos 6 meses)
        $monthlyPurchases = $this->getMonthlyPurchases();
        
        // Partners próximos a cambiar de nivel
        $nearLevelChange = $this->getPartnersNearLevelChange();
        
        // Actividad reciente de cambios de nivel
        $recentTierChanges = $this->getRecentTierChanges();
        
        return view('pricing-dashboard.index', compact(
            'stats',
            'tierDistribution',
            'topPartners',
            'monthlyPurchases',
            'nearLevelChange',
            'recentTierChanges'
        ));
    }

    /**
     * Estadísticas generales
     */
    private function getGeneralStats()
    {
        $partnersCount = Partner::whereIn('type', ['Asociado', 'Mixto'])->count();
        $partnersWithPricing = PartnerPricing::whereNotNull('current_tier_id')->count();
        $currentMonthPurchases = PartnerPricing::sum('current_month_purchases');
        $lastMonthPurchases = PartnerPricing::sum('last_month_purchases');
        
        $avgMarkup = PartnerPricing::avg('markup_percentage');
        
        return [
            'total_partners' => $partnersCount,
            'partners_with_tier' => $partnersWithPricing,
            'partners_without_tier' => $partnersCount - $partnersWithPricing,
            'current_month_purchases' => $currentMonthPurchases,
            'last_month_purchases' => $lastMonthPurchases,
            'avg_markup' => round($avgMarkup, 2),
            'growth_percentage' => $lastMonthPurchases > 0 
                ? round((($currentMonthPurchases - $lastMonthPurchases) / $lastMonthPurchases) * 100, 2)
                : 0
        ];
    }

    /**
     * Distribución de partners por tier
     */
    private function getTierDistribution()
    {
        return PricingTier::withCount('partners')
            ->ordered()
            ->get()
            ->map(function($tier) {
                return [
                    'name' => $tier->name,
                    'count' => $tier->partners_count,
                    'discount' => $tier->discount_percentage,
                ];
            });
    }

    /**
     * Top 10 partners por compras del mes actual
     */
    private function getTopPartners()
    {
        return PartnerPricing::with(['partner', 'currentTier'])
            ->orderBy('current_month_purchases', 'desc')
            ->limit(10)
            ->get()
            ->map(function($pricing) {
                return [
                    'partner' => $pricing->partner,
                    'purchases' => $pricing->current_month_purchases,
                    'tier' => $pricing->currentTier,
                    'markup' => $pricing->markup_percentage,
                ];
            });
    }

    /**
     * Evolución de compras mensuales (últimos 6 meses)
     */
    private function getMonthlyPurchases()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            // Sumar compras de ese período desde el historial
            $total = PartnerTierHistory::whereBetween('period_start', [$startOfMonth, $endOfMonth])
                ->sum('purchases_amount');
            
            $months[] = [
                'month' => $date->format('M Y'),
                'total' => $total,
            ];
        }
        
        return collect($months);
    }

    /**
     * Partners próximos a cambiar de nivel
     */
    private function getPartnersNearLevelChange()
    {
        $partners = PartnerPricing::with(['partner', 'currentTier'])
            ->whereNotNull('current_tier_id')
            ->get()
            ->filter(function($pricing) {
                $currentTier = $pricing->currentTier;
                if (!$currentTier) return false;
                
                $purchases = $pricing->current_month_purchases;
                
                // Cerca de subir de nivel
                if ($currentTier->max_monthly_purchases) {
                    $nextTierThreshold = $currentTier->max_monthly_purchases;
                    $percentageToNext = ($purchases / $nextTierThreshold) * 100;
                    
                    if ($percentageToNext >= 80 && $percentageToNext < 100) {
                        return true;
                    }
                }
                
                // Cerca de bajar de nivel
                $minThreshold = $currentTier->min_monthly_purchases;
                if ($purchases < $minThreshold * 1.1 && $purchases >= $minThreshold) {
                    return true;
                }
                
                return false;
            })
            ->take(5);
        
        return $partners;
    }

    /**
     * Cambios de nivel recientes
     */
    private function getRecentTierChanges()
    {
        return PartnerTierHistory::with(['partner', 'tier'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
}