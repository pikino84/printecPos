<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerEntity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerEntityController extends Controller
{
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
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_default'      => ['sometimes','boolean'],
            'is_active'       => ['sometimes','boolean'],
        ]);

        // Asegura una sola default por partner
        if ($request->boolean('is_default')) {
            $partner->entities()->update(['is_default' => false]);
            $data['is_default'] = true;
        } elseif ($partner->entities()->count() === 0) {
            // primera entidad → márcala default por conveniencia
            $data['is_default'] = false;
        }

        $partner->entities()->create($data);

        return redirect()->route('partners.entities.index', $partner)
            ->with('success','Razón social creada.');
    }

    public function edit(PartnerEntity $entity)
    {
        // shallow route: solo recibimos $entity
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
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_logo' => 'sometimes|boolean',
            'is_default'      => ['sometimes','boolean'],
            'is_active'       => ['sometimes','boolean'],
        ]);

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
        $entity->delete();

        // si borraste la default, marca otra como default
        if (!$partner->defaultEntity()->exists() && $partner->entities()->exists()) {
            $partner->entities()->first()->update(['is_default' => true]);
        }

        return redirect()->route('partners.entities.index', $partner)
            ->with('success','Razón social eliminada.');
    }
}
