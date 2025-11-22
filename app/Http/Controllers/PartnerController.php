<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Partner;
use Illuminate\Support\Facades\Storage;


class PartnerController extends Controller
{
    public function index()
    {
        $this->authorize('partners_index');
        // Si el modelo Partner tiene la relación entities(), usa withCount
        // Si no, simplemente obtén los partners sin el conteo
        $partners = Partner::all();
        return view('partners.index', compact('partners'));
    }

    public function create()
    {
        return view('partners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:partners',
            'contact_name' => 'nullable',
            'contact_phone' => 'nullable',
            'contact_email' => 'nullable|email',
            'direccion' => 'nullable',
            'type' => 'required|in:Proveedor,Asociado,Mixto',
            'commercial_terms' => 'nullable',
            'comments' => 'nullable',
            'is_active' => 'nullable|boolean',
        ]);
        $data['slug'] = Str::slug($data['name']);
        
        Partner::create($data);
        return redirect()->route('partners.index')->with('success', 'Partner creado.');
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Partner $partner)
    {
        $partner->load(['users', 'entities', 'warehouses', 'products', 'pricing.currentTier']);
        
        $stats = $partner->getStats();
        
        return view('partners.show', compact('partner', 'stats'));
    }

    public function edit(Partner $partner)
    {
        return view('partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'name' => 'required|unique:partners,name,' . $partner->id,
            'contact_name' => 'nullable',
            'contact_phone' => 'nullable',
            'contact_email' => 'nullable|email',
            'direccion' => 'nullable',
            'type' => 'required|in:Proveedor,Asociado,Mixto',
            'commercial_terms' => 'nullable',
            'comments' => 'nullable',
            'is_active' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        $partner->update($data);

        return redirect()->route('partners.index')->with('success', 'Partner actualizado.');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();
        return redirect()->route('partners.index')->with('success', 'Partner eliminado.');
    }
}