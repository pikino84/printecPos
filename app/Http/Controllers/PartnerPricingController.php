<?php

namespace App\Http\Controllers;

use App\Models\CartSession;
use App\Models\Partner;
use App\Models\PartnerPricing;
use App\Models\PricingTier;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class PartnerPricingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super admin|admin')->except(['myMarkup', 'updateMyMarkup']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PartnerPricing::with(['partner', 'currentTier'])
            ->whereHas('partner', function($q) {
                $q->whereIn('type', ['Asociado', 'Mixto']);
            });

        // Filtro por tier
        if ($request->filled('tier_id')) {
            $query->where('current_tier_id', $request->tier_id);
        }

        // Filtro por búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('partner', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'partner.name');
        $sortDirection = $request->get('direction', 'asc');

        switch ($sortField) {
            case 'markup':
                $query->orderBy('markup_percentage', $sortDirection);
                break;
            case 'purchases':
                $query->orderBy('current_month_purchases', $sortDirection);
                break;
            case 'tier':
                $query->orderBy('current_tier_id', $sortDirection);
                break;
            default:
                $query->join('partners', 'partner_pricing.partner_id', '=', 'partners.id')
                    ->orderBy('partners.name', $sortDirection)
                    ->select('partner_pricing.*');
                break;
        }

        $pricings = $query->paginate(15);
        $tiers = PricingTier::active()->ordered()->get();

        return view('partner-pricing.index', compact('pricings', 'tiers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partner $partner)
    {
        $pricing = $partner->getPricingConfig();
        $tiers = PricingTier::active()->ordered()->get();
        
        return view('partner-pricing.edit', compact('partner', 'pricing', 'tiers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partner $partner)
    {
        $request->validate([
            'markup_percentage' => 'required|numeric|min:0|max:100',
            'current_tier_id' => 'nullable|exists:pricing_tiers,id',
            'manual_tier_override' => 'boolean',
        ]);

        $pricing = $partner->getPricingConfig();
        
        $oldTierId = $pricing->current_tier_id;
        $newTierId = $request->current_tier_id;
        
        $pricing->update([
            'markup_percentage' => $request->markup_percentage,
            'current_tier_id' => $request->current_tier_id,
            'manual_tier_override' => $request->boolean('manual_tier_override'),
            'tier_assigned_at' => $newTierId != $oldTierId ? now() : $pricing->tier_assigned_at,
        ]);

        // Si cambió el tier manualmente, registrar en historial
        if ($newTierId != $oldTierId && $request->boolean('manual_tier_override')) {
            \App\Models\PartnerTierHistory::recordTierAssignment(
                $partner->id,
                $newTierId,
                $pricing->last_month_purchases,
                now()->startOfMonth(),
                now()->endOfMonth(),
                true,
                'Asignación manual por administrador'
            );
        }

        return redirect()->route('partner-pricing.index')
            ->with('success', 'Configuración de pricing actualizada exitosamente.');
    }

    /**
     * Mostrar historial de un partner
     */
    public function history(Partner $partner)
    {
        $pricing = $partner->getPricingConfig();
        
        $history = \App\Models\PartnerTierHistory::where('partner_id', $partner->id)
            ->with('tier')
            ->orderBy('period_start', 'desc')
            ->paginate(20);

        return view('partner-pricing.history', compact('partner', 'pricing', 'history'));
    }

    /**
     * Resetear compras del mes actual (para testing)
     */
    public function resetPurchases(Partner $partner)
    {
        $pricing = $partner->getPricingConfig();
        $pricing->current_month_purchases = 0;
        $pricing->save();

        return back()->with('success', 'Compras del mes actual reseteadas.');
    }

    /**
     * Mostrar formulario para que el asociado edite su propio markup
     */
    public function myMarkup()
    {
        $user = auth()->user();
        $partner = $user->partner;

        if (!$partner) {
            abort(403, 'No tienes un partner asociado.');
        }

        $pricing = $partner->getPricingConfig();

        return view('partner-pricing.my-markup', compact('partner', 'pricing'));
    }

    /**
     * Actualizar el markup del asociado
     */
    public function updateMyMarkup(Request $request)
    {
        $request->validate([
            'markup_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $user = auth()->user();
        $partner = $user->partner;

        if (!$partner) {
            abort(403, 'No tienes un partner asociado.');
        }

        $pricing = $partner->getPricingConfig();
        $pricing->update([
            'markup_percentage' => $request->markup_percentage,
        ]);

        // Recalcular precios del carrito del usuario con el nuevo porcentaje de ganancia
        $this->recalculateCartPrices($user->id, $partner);

        return back()->with('success', 'Tu porcentaje de ganancia ha sido actualizado.');
    }

    /**
     * Recalcular precios del carrito según el nuevo markup
     */
    private function recalculateCartPrices($userId, $partner)
    {
        $cartItems = CartSession::where('user_id', $userId)
            ->with('variant.product')
            ->get();

        $partnerPricing = $partner->getPricingConfig();

        foreach ($cartItems as $item) {
            $variant = $item->variant;
            $product = $variant->product;
            $isPrintecProduct = !$product->is_own_product;

            $newPrice = $partnerPricing->calculateSalePrice($variant->price, $isPrintecProduct);
            $item->update(['unit_price' => $newPrice]);
        }
    }
}