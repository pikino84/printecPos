<?php

namespace App\Http\Controllers;

use App\Models\PricingTier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PricingTierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super admin|admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiers = PricingTier::withCount('partners')
            ->ordered()
            ->get();

        return view('pricing-tiers.index', compact('tiers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pricing-tiers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:pricing_tiers,name',
            'min_monthly_purchases' => 'required|numeric|min:0',
            'max_monthly_purchases' => 'nullable|numeric|gt:min_monthly_purchases',
            'markup_percentage' => 'required|numeric|min:0|max:100',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'max_monthly_purchases.gt' => 'El máximo debe ser mayor que el mínimo',
        ]);

        $tier = PricingTier::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'min_monthly_purchases' => $request->min_monthly_purchases,
            'max_monthly_purchases' => $request->max_monthly_purchases,
            'markup_percentage' => $request->markup_percentage,
            'discount_percentage' => $request->discount_percentage,
            'description' => $request->description,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('pricing-tiers.index')
            ->with('success', 'Nivel de precio creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PricingTier $pricingTier)
    {
        $pricingTier->load(['partners.partner', 'history' => function($query) {
            $query->latest()->limit(20);
        }]);

        return view('pricing-tiers.show', compact('pricingTier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PricingTier $pricingTier)
    {
        return view('pricing-tiers.edit', compact('pricingTier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PricingTier $pricingTier)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:pricing_tiers,name,' . $pricingTier->id,
            'min_monthly_purchases' => 'required|numeric|min:0',
            'max_monthly_purchases' => 'nullable|numeric|gt:min_monthly_purchases',
            'markup_percentage' => 'required|numeric|min:0|max:100',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'max_monthly_purchases.gt' => 'El máximo debe ser mayor que el mínimo',
        ]);

        $pricingTier->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'min_monthly_purchases' => $request->min_monthly_purchases,
            'max_monthly_purchases' => $request->max_monthly_purchases,
            'markup_percentage' => $request->markup_percentage,
            'discount_percentage' => $request->discount_percentage,
            'description' => $request->description,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('pricing-tiers.index')
            ->with('success', 'Nivel de precio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PricingTier $pricingTier)
    {
        // Verificar si tiene partners asignados
        if ($pricingTier->partners()->count() > 0) {
            return back()->with('error', 'No se puede eliminar este nivel porque tiene partners asignados.');
        }

        $pricingTier->delete();

        return redirect()->route('pricing-tiers.index')
            ->with('success', 'Nivel de precio eliminado exitosamente.');
    }
}