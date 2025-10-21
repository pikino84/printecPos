<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $partnerId = $user->partner_id;
        
        $query = Client::with('partners')
            ->active();

        // Si el usuario no es super admin, solo ve clientes de su partner
        if (!$user->hasRole('super admin')) {
            $query->forPartner($partnerId);
        }

        // Búsqueda
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtro por partner (solo para super admin)
        if ($request->filled('partner_id') && $user->hasRole('super admin')) {
            $query->forPartner($request->partner_id);
        }

        $clients = $query->orderBy('nombre')->paginate(20);

        // Para el filtro de partners (solo super admin)
        $partners = $user->hasRole('super admin') 
            ? Partner::asociadosYMixtos()->orderBy('name')->get()
            : collect();

        return view('clients.index', compact('clients', 'partners'));
    }

    public function create()
    {
        $user = Auth::user();
        
        // Obtener partners disponibles según el rol
        if ($user->hasRole('super admin')) {
            $partners = Partner::asociadosYMixtos()->active()->orderBy('name')->get();
        } else {
            $partners = collect([$user->partner]);
        }

        return view('clients.create', compact('partners'));
    }

    public function store(Request $request)
    {
        // Convertir RFC a mayúsculas antes de validar
        if ($request->filled('rfc')) {
            $request->merge(['rfc' => strtoupper(trim($request->rfc))]);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:30',
            'razon_social' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13|regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
            'direccion' => 'nullable|string',
            'notas' => 'nullable|string',
            'partner_ids' => 'required|array|min:1',
            'partner_ids.*' => 'exists:partners,id',
        ], [
            'rfc.regex' => 'El formato del RFC no es válido. Debe ser mayúsculas y números (ej: XAXX010101000)',
        ]);

        try {
            DB::beginTransaction();

            // Verificar si ya existe un cliente con el mismo email o RFC
            $existingClient = null;
            if (!empty($validated['email'])) {
                $existingClient = Client::where('email', $validated['email'])->first();
            }
            if (!$existingClient && !empty($validated['rfc'])) {
                $existingClient = Client::where('rfc', $validated['rfc'])->first();
            }

            if ($existingClient) {
                // Cliente ya existe, solo agregar los nuevos partners
                foreach ($validated['partner_ids'] as $partnerId) {
                    $existingClient->addPartner($partnerId);
                }
                
                DB::commit();
                
                return redirect()
                    ->route('clients.show', $existingClient)
                    ->with('info', 'El cliente ya existía. Se agregó la relación con tu partner.');
            }

            // Crear nuevo cliente
            $client = Client::create([
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'],
                'razon_social' => $validated['razon_social'],
                'rfc' => $validated['rfc'],
                'direccion' => $validated['direccion'],
                'notas' => $validated['notas'],
            ]);

            // Adjuntar partners
            foreach ($validated['partner_ids'] as $partnerId) {
                $client->partners()->attach($partnerId, [
                    'first_contact_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('clients.show', $client)
                ->with('success', 'Cliente creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al crear el cliente: ' . $e->getMessage());
        }
    }

    public function show(Client $client)
    {
        $user = Auth::user();
        
        // Verificar acceso
        if (!$user->hasRole('super admin')) {
            if (!$client->partners()->where('partner_id', $user->partner_id)->exists()) {
                abort(403, 'No tienes permiso para ver este cliente.');
            }
        }

        $client->load(['partners', 'quotes']);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $user = Auth::user();
        
        // Verificar acceso
        if (!$user->hasRole('super admin')) {
            if (!$client->partners()->where('partner_id', $user->partner_id)->exists()) {
                abort(403, 'No tienes permiso para editar este cliente.');
            }
        }

        // Obtener partners disponibles
        if ($user->hasRole('super admin')) {
            $partners = Partner::asociadosYMixtos()->active()->orderBy('name')->get();
        } else {
            $partners = collect([$user->partner]);
        }

        $selectedPartnerIds = $client->partners->pluck('id')->toArray();

        return view('clients.edit', compact('client', 'partners', 'selectedPartnerIds'));
    }

    public function update(Request $request, Client $client)
    {
        // Convertir RFC a mayúsculas antes de validar
        if ($request->filled('rfc')) {
            $request->merge(['rfc' => strtoupper(trim($request->rfc))]);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:30',
            'razon_social' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13|regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
            'direccion' => 'nullable|string',
            'notas' => 'nullable|string',
            'partner_ids' => 'required|array|min:1',
            'partner_ids.*' => 'exists:partners,id',
            'is_active' => 'boolean',
        ], [
            'rfc.regex' => 'El formato del RFC no es válido. Debe ser mayúsculas y números (ej: XAXX010101000)',
        ]);

        try {
            DB::beginTransaction();

            $client->update([
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'],
                'razon_social' => $validated['razon_social'],
                'rfc' => $validated['rfc'],
                'direccion' => $validated['direccion'],
                'notas' => $validated['notas'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Sincronizar partners manteniendo first_contact_at
            $syncData = [];
            foreach ($validated['partner_ids'] as $partnerId) {
                $existing = $client->partners()->where('partner_id', $partnerId)->first();
                $syncData[$partnerId] = [
                    'first_contact_at' => $existing ? $existing->pivot->first_contact_at : now(),
                ];
            }
            $client->partners()->sync($syncData);

            DB::commit();

            return redirect()
                ->route('clients.show', $client)
                ->with('success', 'Cliente actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el cliente: ' . $e->getMessage());
        }
    }

    // API endpoint para búsqueda rápida en cotizaciones
    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q', '');

        $query = Client::active();

        // Filtrar por partner del usuario si no es super admin
        if (!$user->hasRole('super admin')) {
            $query->forPartner($user->partner_id);
        }

        if ($search) {
            $query->search($search);
        }

        $clients = $query
            ->with('partners:id,name')
            ->limit(10)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'text' => $client->nombre_completo,
                    'email' => $client->email,
                    'telefono' => $client->telefono,
                    'rfc' => $client->rfc,
                    'razon_social' => $client->razon_social,
                    'partners' => $client->partners->pluck('name')->join(', '),
                ];
            });

        return response()->json($clients);
    }

    public function destroy(Client $client)
    {
        $user = Auth::user();
        
        // Solo super admin puede eliminar
        if (!$user->hasRole('super admin')) {
            abort(403, 'No tienes permiso para eliminar clientes.');
        }

        try {
            // Soft delete (desactivar)
            $client->update(['is_active' => false]);

            return redirect()
                ->route('clients.index')
                ->with('success', 'Cliente desactivado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al desactivar el cliente: ' . $e->getMessage());
        }
    }
}