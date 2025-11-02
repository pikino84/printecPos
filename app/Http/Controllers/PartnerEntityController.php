<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerEntity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PartnerEntityController extends Controller
{
    // ========================================================================
    // MÉTODOS PARA SUPER ADMIN (Partners -> Entities)
    // ========================================================================
    
    public function index(Partner $partner)
    {
        $entities = $partner->entities()->latest()->get();
        return view('partners.entities.index', compact('partner','entities'));
    }

    public function create(Partner $partner)
    {
        return view('partners.entities.create', compact('partner'));
    }

    public function store(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'razon_social'    => ['required','string',
                Rule::unique('partner_entities')->where(fn($q)=>$q->where('partner_id',$partner->id))
            ],
            'rfc'             => ['nullable','string','max:20'],
            'telefono'        => ['nullable','string'],
            'correo_contacto' => ['nullable','email'],
            'direccion'       => ['nullable','string'],
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_default'      => ['sometimes','boolean'],
            'is_active'       => ['sometimes','boolean'],
        ]);
        
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public');
        }

        // Asegura una sola default por partner
        if ($request->boolean('is_default')) {
            $partner->entities()->update(['is_default' => false]);
            $data['is_default'] = true;
        } elseif ($partner->entities()->count() === 0) {
            $data['is_default'] = true;
        }

        $partner->entities()->create($data);

        return redirect()->route('partners.entities.index', $partner)
            ->with('success','Razón social creada.');
    }

    public function edit(PartnerEntity $entity)
    {
        $partner = $entity->partner;
        return view('partners.entities.edit', compact('partner','entity'));
    }

    public function update(Request $request, PartnerEntity $entity)
    {
        $partner = $entity->partner;

        $data = $request->validate([
            'razon_social'    => ['required','string',
                Rule::unique('partner_entities')
                    ->ignore($entity->id)
                    ->where(fn($q)=>$q->where('partner_id',$partner->id))
            ],
            'rfc'             => ['nullable','string','max:20'],
            'telefono'        => ['nullable','string'],
            'correo_contacto' => ['nullable','email'],
            'direccion'       => ['nullable','string'],
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_logo'     => 'sometimes|boolean',
            'is_default'      => ['sometimes','boolean'],
            'is_active'       => ['sometimes','boolean'],
        ]);

        if ($request->hasFile('logo')) {
            if ($entity->logo_path) {
                Storage::disk('public')->delete($entity->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public');
        }
    
        if ($request->boolean('remove_logo') && $entity->logo_path) {
            Storage::disk('public')->delete($entity->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->boolean('is_default')) {
            $partner->entities()->where('id','!=',$entity->id)->update(['is_default' => false]);
            $data['is_default'] = true;
        }
        
        $entity->update($data);

        return redirect()->route('partners.entities.index', $partner)
            ->with('success','Razón social actualizada.');
    }

    public function destroy(PartnerEntity $entity)
    {
        $partner = $entity->partner;
        
        if ($entity->logo_path) {
            Storage::disk('public')->delete($entity->logo_path);
        }
        
        $entity->delete();

        // Si borraste la default, marca otra como default
        if (!$partner->defaultEntity()->exists() && $partner->entities()->exists()) {
            $partner->entities()->first()->update(['is_default' => true]);
        }

        return redirect()->route('partners.entities.index', $partner)
            ->with('success','Razón social eliminada.');
    }

    // ========================================================================
    // MÉTODOS PARA ASOCIADOS (Mis Razones Sociales)
    // ========================================================================
    
    /**
     * Lista de razones sociales del partner del usuario autenticado
     */
    public function myIndex()
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        if (!$partner) {
            abort(403, 'No tienes un partner asignado.');
        }

        $entities = $partner->entities()->latest()->get();
        
        return view('my-entities.index', compact('partner', 'entities'));
    }

    /**
     * Formulario para crear razón social (asociado)
     */
    public function myCreate()
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        if (!$partner) {
            abort(403, 'No tienes un partner asignado.');
        }

        return view('my-entities.create', compact('partner'));
    }

    /**
     * Guardar razón social (asociado)
     */
    public function myStore(Request $request)
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        if (!$partner) {
            abort(403, 'No tienes un partner asignado.');
        }

        $data = $request->validate([
            'razon_social'    => ['required','string',
                Rule::unique('partner_entities')->where(fn($q)=>$q->where('partner_id',$partner->id))
            ],
            'rfc'             => ['nullable','string','max:20'],
            'telefono'        => ['nullable','string'],
            'correo_contacto' => ['nullable','email'],
            'direccion'       => ['nullable','string'],
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_default'      => ['sometimes','boolean'],
        ]);
        
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public');
        }

        // Asegura una sola default por partner
        if ($request->boolean('is_default')) {
            $partner->entities()->update(['is_default' => false]);
            $data['is_default'] = true;
        } elseif ($partner->entities()->count() === 0) {
            $data['is_default'] = true;
        }

        $data['is_active'] = true; // Siempre activo cuando lo crea el asociado

        $partner->entities()->create($data);

        return redirect()->route('my-entities.index')
            ->with('success','Razón social creada exitosamente.');
    }

    /**
     * Formulario para editar razón social (asociado)
     */
    public function myEdit($id)
    {
        $user = Auth::user();
        $entity = PartnerEntity::findOrFail($id);
        
        // Verificar que la entidad pertenezca al partner del usuario
        if ($entity->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para editar esta razón social.');
        }

        $partner = $user->partner;

        return view('my-entities.edit', compact('partner', 'entity'));
    }

    /**
     * Actualizar razón social (asociado)
     */
    public function myUpdate(Request $request, $id)
    {
        $user = Auth::user();
        $entity = PartnerEntity::findOrFail($id);
        
        // Verificar que la entidad pertenezca al partner del usuario
        if ($entity->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para editar esta razón social.');
        }

        $partner = $user->partner;

        $data = $request->validate([
            'razon_social'    => ['required','string',
                Rule::unique('partner_entities')
                    ->ignore($entity->id)
                    ->where(fn($q)=>$q->where('partner_id',$partner->id))
            ],
            'rfc'             => ['nullable','string','max:20'],
            'telefono'        => ['nullable','string'],
            'correo_contacto' => ['nullable','email'],
            'direccion'       => ['nullable','string'],
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_logo'     => 'sometimes|boolean',
            'is_default'      => ['sometimes','boolean'],
        ]);

        if ($request->hasFile('logo')) {
            if ($entity->logo_path) {
                Storage::disk('public')->delete($entity->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public');
        }
    
        if ($request->boolean('remove_logo') && $entity->logo_path) {
            Storage::disk('public')->delete($entity->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->boolean('is_default')) {
            $partner->entities()->where('id','!=',$entity->id)->update(['is_default' => false]);
            $data['is_default'] = true;
        }
        
        $entity->update($data);

        return redirect()->route('my-entities.index')
            ->with('success','Razón social actualizada exitosamente.');
    }

    /**
     * Eliminar razón social (asociado - solo Asociado Administrador)
     */
    public function myDestroy($id)
    {
        $user = Auth::user();
        $entity = PartnerEntity::findOrFail($id);
        
        // Verificar que la entidad pertenezca al partner del usuario
        if ($entity->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para eliminar esta razón social.');
        }

        $partner = $user->partner;
        
        if ($entity->logo_path) {
            Storage::disk('public')->delete($entity->logo_path);
        }
        
        $entity->delete();

        // Si borraste la default, marca otra como default
        if (!$partner->defaultEntity()->exists() && $partner->entities()->exists()) {
            $partner->entities()->first()->update(['is_default' => true]);
        }

        return redirect()->route('my-entities.index')
            ->with('success','Razón social eliminada exitosamente.');
    }
}