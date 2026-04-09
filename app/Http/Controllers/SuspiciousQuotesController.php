<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuspiciousQuotesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super admin');
    }

    public function index(Request $request)
    {
        $stats = $this->getStats();
        $staleQuotes = $this->getStaleQuotes($request);
        $conversionByPartner = $this->getConversionByPartner();
        $topOffenders = $this->getTopOffenders();
        $monthlyLeakage = $this->getMonthlyLeakage();

        return view('suspicious-quotes.index', compact(
            'stats',
            'staleQuotes',
            'conversionByPartner',
            'topOffenders',
            'monthlyLeakage'
        ));
    }

    /**
     * Statuses que indican cotizaciones sin cierre (posibles fugas).
     * "sent" = enviada sin respuesta, "expired" = venció sin acción.
     */
    private const STALE_STATUSES = ['sent', 'expired'];

    /**
     * Estadísticas generales de cotizaciones.
     */
    private function getStats(): array
    {
        $total = Quote::count();
        $sent = Quote::where('status', 'sent')->count();
        $expired = Quote::where('status', 'expired')->count();
        $accepted = Quote::where('status', 'accepted')->count();
        $invoiced = Quote::where('status', 'invoiced')->count();
        $paid = Quote::where('status', 'paid')->count();

        // Cotizaciones sin cierre (enviadas o expiradas)
        $stale = Quote::whereIn('status', self::STALE_STATUSES)->count();

        // Monto total en cotizaciones sin cierre
        $staleAmount = Quote::whereIn('status', self::STALE_STATUSES)->sum('total');

        // Tasa de conversión general
        $conversionRate = $total > 0
            ? round((($accepted + $invoiced + $paid) / $total) * 100, 1)
            : 0;

        return compact('total', 'sent', 'expired', 'accepted', 'invoiced', 'paid', 'stale', 'staleAmount', 'conversionRate');
    }

    /**
     * Cotizaciones sin cierre (enviadas o expiradas).
     * Ordenadas por monto descendente para priorizar las más grandes.
     */
    private function getStaleQuotes(Request $request)
    {
        $days = (int) ($request->input('days', 0));
        $status = $request->input('filter_status', '');

        $query = Quote::with(['user', 'partner', 'client'])
            ->whereIn('status', self::STALE_STATUSES);

        if ($days > 0) {
            $query->where('created_at', '<', Carbon::now()->subDays($days));
        }

        if ($status && in_array($status, self::STALE_STATUSES)) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('total')
            ->paginate(20)
            ->appends(['days' => $days, 'filter_status' => $status]);
    }

    /**
     * Tasa de conversión por partner.
     * Partners con muchas cotizaciones enviadas y pocas aceptadas son sospechosos.
     */
    private function getConversionByPartner(): array
    {
        $partners = Partner::withCount([
            'quotes as total_quotes',
            'quotes as stale_quotes' => function ($q) {
                $q->whereIn('status', self::STALE_STATUSES);
            },
            'quotes as converted_quotes' => function ($q) {
                $q->whereIn('status', ['accepted', 'invoiced', 'paid']);
            },
        ])
        ->having('total_quotes', '>', 0)
        ->orderByDesc('stale_quotes')
        ->get()
        ->map(function ($partner) {
            $partner->conversion_rate = $partner->total_quotes > 0
                ? round(($partner->converted_quotes / $partner->total_quotes) * 100, 1)
                : 0;
            $partner->stale_amount = Quote::where('partner_id', $partner->id)
                ->whereIn('status', self::STALE_STATUSES)
                ->sum('total');
            return $partner;
        })
        ->toArray();

        return $partners;
    }

    /**
     * Top partners con más cotizaciones estancadas (posibles fugas).
     */
    private function getTopOffenders(): array
    {
        return Quote::whereIn('status', self::STALE_STATUSES)
            ->select('partner_id', DB::raw('COUNT(*) as stale_count'), DB::raw('SUM(total) as stale_total'))
            ->groupBy('partner_id')
            ->orderByDesc('stale_total')
            ->with('partner:id,name')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Estimación mensual de posible fuga (cotizaciones enviadas sin cierre).
     */
    private function getMonthlyLeakage(): array
    {
        return Quote::whereIn('status', self::STALE_STATUSES)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }
}
