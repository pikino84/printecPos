<?php

namespace App\Http\Controllers;

use App\Mail\PartnerActivated;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('partners_index');

        $partners = Partner::with(['entities.bankAccounts'])->get();

        // Calcular % de completitud del perfil para cada partner.
        $partners->each(function ($partner) {
            $partner->setAttribute('profile_completion', $partner->profileCompletionPercentage());
        });

        // Filtros por rango de % (útil para campañas — "los que están en 0%").
        $profileMin = $request->integer('profile_min');
        $profileMax = $request->integer('profile_max');
        if ($request->filled('profile_min')) {
            $partners = $partners->filter(fn ($p) => $p->profile_completion >= $profileMin);
        }
        if ($request->filled('profile_max')) {
            $partners = $partners->filter(fn ($p) => $p->profile_completion <= $profileMax);
        }

        // Orden por columna `profile` o por nombre (default).
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        $partners = $sort === 'profile'
            ? $partners->sortBy('profile_completion', SORT_REGULAR, $direction === 'desc')
            : $partners->sortBy('name', SORT_REGULAR, $direction === 'desc');

        $partners = $partners->values();

        return view('partners.index', compact('partners', 'sort', 'direction', 'profileMin', 'profileMax'));
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
        $wasInactive = ! $partner->is_active;
        $data = $request->validate([
            'name' => 'required|unique:partners,name,'.$partner->id,
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

        // Épica 05: si estaba vetado y el admin acaba de completar su perfil al 100%,
        // levantar el veto y reactivar (el distribuidor vetado no puede hacerlo él mismo).
        $vetoLifted = $partner->liftVetoIfProfileComplete();

        // Si el partner fue activado (por el form o al levantar el veto), activar sus usuarios
        if ($wasInactive && $partner->is_active) {
            $this->activatePartnerUsers($partner);
        }

        // Si el partner fue desactivado, desactivar también sus usuarios
        if (! $wasInactive && ! $partner->is_active) {
            $this->deactivatePartnerUsers($partner);
        }

        $message = $vetoLifted
            ? 'Partner actualizado. Perfil completo al 100%: se levantó el veto y se reactivó la cuenta.'
            : 'Partner actualizado.';

        return redirect()->route('partners.index')->with('success', $message);
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

    /**
     * Export CSV de partners filtrados por % de completitud (épica 05.6).
     * Usado por el equipo de ventas para campañas dirigidas a partners en 0% / parciales.
     */
    public function exportProfileProgress(Request $request): StreamedResponse
    {
        $this->authorize('partners_index');

        // Solo Asociados puros: Mixto y Proveedor están exentos de la regla de perfil con deadline.
        $partners = Partner::with(['entities.bankAccounts'])
            ->where('type', 'Asociado')
            ->get()
            ->map(function (Partner $p) {
                $missing = $p->missingProfileFields();
                $missingFlat = collect($missing)->flatMap(fn ($fields) => $fields)->implode(', ');

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'type' => $p->type,
                    'contact_name' => $p->contact_name,
                    'contact_email' => $p->contact_email,
                    'contact_phone' => $p->contact_phone,
                    'profile_completion' => $p->profileCompletionPercentage(),
                    'missing_fields' => $missingFlat,
                    'profile_deadline_at' => $p->profile_deadline_at?->format('Y-m-d'),
                    'days_until_deadline' => $p->daysUntilDeadline(),
                    'is_active' => $p->is_active ? 'sí' : 'no',
                    'vetoed_until' => $p->vetoed_until?->format('Y-m-d'),
                ];
            });

        $min = $request->integer('profile_min');
        $max = $request->integer('profile_max');
        if ($request->filled('profile_min')) {
            $partners = $partners->filter(fn ($r) => $r['profile_completion'] >= $min);
        }
        if ($request->filled('profile_max')) {
            $partners = $partners->filter(fn ($r) => $r['profile_completion'] <= $max);
        }

        $filename = 'partners-perfil-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($partners) {
            $handle = fopen('php://output', 'w');
            // BOM para que Excel detecte UTF-8.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'ID', 'Nombre', 'Tipo', 'Contacto', 'Correo', 'Teléfono',
                '% Perfil', 'Faltantes', 'Deadline', 'Días restantes',
                'Activo', 'Vetado hasta',
            ]);
            foreach ($partners as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
