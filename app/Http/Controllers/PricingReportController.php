<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerPricing;
use App\Models\PricingTier;
use App\Models\PartnerTierHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PricingReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super admin|admin');
    }

    /**
     * Reporte de historial de niveles
     */
    public function tierHistory(Request $request)
    {
        $query = PartnerTierHistory::with(['partner', 'tier']);

        // Filtro por partner
        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        // Filtro por tier
        if ($request->filled('tier_id')) {
            $query->where('tier_id', $request->tier_id);
        }

        // Filtro por fecha
        if ($request->filled('date_from')) {
            $query->where('period_start', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('period_end', '<=', $request->date_to);
        }

        // Filtro por tipo (manual/automático)
        if ($request->filled('type')) {
            $isManual = $request->type === 'manual';
            $query->where('is_manual', $isManual);
        }

        $history = $query->orderBy('created_at', 'desc')->paginate(20);

        $partners = Partner::whereIn('type', ['Asociado', 'Mixto'])
            ->orderBy('name')
            ->get();
        
        $tiers = PricingTier::active()->ordered()->get();

        return view('pricing-reports.tier-history', compact('history', 'partners', 'tiers'));
    }

    /**
     * Reporte de compras mensuales
     */
    public function monthlyPurchases(Request $request)
    {
        // Período por defecto: últimos 12 meses
        $monthsBack = $request->filled('months') ? (int)$request->months : 12;
        
        $data = [];
        
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            // Partners por tier en ese mes
            $tierBreakdown = PartnerTierHistory::with('tier')
                ->whereBetween('period_start', [$startOfMonth, $endOfMonth])
                ->get()
                ->groupBy('tier_id')
                ->map(function($group) {
                    return [
                        'tier' => $group->first()->tier,
                        'count' => $group->count(),
                        'total_purchases' => $group->sum('purchases_amount'),
                    ];
                });
            
            $totalPurchases = $tierBreakdown->sum('total_purchases');
            
            $data[] = [
                'month' => $date->format('M Y'),
                'total_purchases' => $totalPurchases,
                'tier_breakdown' => $tierBreakdown,
                'partners_count' => $tierBreakdown->sum('count'),
            ];
        }
        
        $data = collect($data);
        
        return view('pricing-reports.monthly-purchases', compact('data', 'monthsBack'));
    }

    /**
     * Reporte de evolución de partners
     */
    public function partnerEvolution(Request $request)
    {
        $partnerId = $request->partner_id;
        
        if (!$partnerId) {
            $partners = Partner::whereIn('type', ['Asociado', 'Mixto'])
                ->orderBy('name')
                ->get();
            
            return view('pricing-reports.partner-evolution', compact('partners'));
        }
        
        $partner = Partner::with('pricing.currentTier')->findOrFail($partnerId);
        
        // Historial del partner
        $history = PartnerTierHistory::where('partner_id', $partnerId)
            ->with('tier')
            ->orderBy('period_start', 'desc')
            ->get();
        
        // Estadísticas
        $stats = [
            'total_periods' => $history->count(),
            'total_purchases' => $history->sum('purchases_amount'),
            'avg_purchases' => $history->avg('purchases_amount'),
            'highest_tier' => $history->sortByDesc('tier.order')->first()?->tier,
            'lowest_tier' => $history->sortBy('tier.order')->first()?->tier,
        ];
        
        $partners = Partner::whereIn('type', ['Asociado', 'Mixto'])
            ->orderBy('name')
            ->get();
        
        return view('pricing-reports.partner-evolution', compact('partner', 'history', 'stats', 'partners'));
    }

    /**
     * Exportar reporte a CSV
     */
    public function exportTierHistory(Request $request)
    {
        $query = PartnerTierHistory::with(['partner', 'tier']);

        // Aplicar mismos filtros que en tierHistory
        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }
        if ($request->filled('tier_id')) {
            $query->where('tier_id', $request->tier_id);
        }
        if ($request->filled('date_from')) {
            $query->where('period_start', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('period_end', '<=', $request->date_to);
        }
        if ($request->filled('type')) {
            $isManual = $request->type === 'manual';
            $query->where('is_manual', $isManual);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        $filename = 'tier-history-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, ['Partner', 'Nivel', 'Período Inicio', 'Período Fin', 'Compras', 'Tipo', 'Notas', 'Fecha Asignación']);
            
            // Datos
            foreach ($data as $record) {
                fputcsv($file, [
                    $record->partner->name,
                    $record->tier->name,
                    $record->period_start->format('Y-m-d'),
                    $record->period_end->format('Y-m-d'),
                    number_format($record->purchases_amount, 2),
                    $record->is_manual ? 'Manual' : 'Automático',
                    $record->notes ?? '',
                    $record->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}