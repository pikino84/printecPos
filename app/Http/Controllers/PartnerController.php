<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Partner;
use App\Models\User;
use App\Mail\PartnerActivated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    public function index()
    {
        $this->authorize('partners_index');
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
        // Guardar estado anterior para detectar activación
        $wasInactive = !$partner->is_active;
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
        $data['is_active'] = $request->boolean('is_active');
        
        $partner->update($data);

        // Si el partner fue activado, activar también sus usuarios y enviar email
        if ($wasInactive && $partner->is_active) {
            $this->activatePartnerUsers($partner);
        }
        
        // Si el partner fue desactivado, desactivar también sus usuarios
        if (!$wasInactive && !$partner->is_active) {
            $this->deactivatePartnerUsers($partner);
        }

        return redirect()->route('partners.index')->with('success', 'Partner actualizado.');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();
        return redirect()->route('partners.index')->with('success', 'Partner eliminado.');
    }

    /**
     * Activar usuarios del partner y enviar email de bienvenida
     */
    private function activatePartnerUsers(Partner $partner): void
    {
        // Obtener usuarios del partner
        $users = User::where('partner_id', $partner->id)->get();

        foreach ($users as $user) {
            // Activar usuario
            $user->update(['is_active' => true]);

            // Enviar email de activación (en cola)
            Mail::to($user->email)->queue(new PartnerActivated($partner, $user));
        }
    }

    /**
     * Desactivar usuarios del partner
     */
    private function deactivatePartnerUsers(Partner $partner): void
    {
        User::where('partner_id', $partner->id)->update(['is_active' => false]);
    }

    /**
     * Generar nueva API key para el partner
     */
    public function generateApiKey(Partner $partner)
    {
        $this->authorize('partners_index');

        $partner->generateApiKey();

        return redirect()->route('partners.show', $partner)
            ->with('success', 'API key generada exitosamente.');
    }

    /**
     * Revocar API key del partner
     */
    public function revokeApiKey(Partner $partner)
    {
        $this->authorize('partners_index');

        $partner->revokeApiKey();

        return redirect()->route('partners.show', $partner)
            ->with('success', 'API key revocada exitosamente.');
    }

    /**
     * Actualizar configuración de API (mostrar precios)
     */
    public function updateApiSettings(Request $request, Partner $partner)
    {
        $this->authorize('partners_index');

        $partner->update([
            'api_show_prices' => $request->boolean('api_show_prices'),
        ]);

        return redirect()->route('partners.show', $partner)
            ->with('success', 'Configuración de API actualizada.');
    }
}