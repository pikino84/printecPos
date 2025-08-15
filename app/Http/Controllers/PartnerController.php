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
        $partners = Partner::withCount('entities')->get();
        return view('partners.index', compact('partners'));
    }

    public function create()
    {
        return view('partners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_comercial' => 'required|unique:partners',
            'razon_social' => 'nullable',
            'rfc' => 'nullable',
            'telefono' => 'nullable',
            'correo_contacto' => 'nullable|email',
            'direccion' => 'nullable',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tipo' => 'required|in:proveedor,asociado,mixto',
            'condiciones_comerciales' => 'nullable',
            'comentarios' => 'nullable',
            'is_active' => 'nullable|boolean',
        ]);
        $data['slug'] = Str::slug($data['nombre_comercial']);
        // subir logo si viene
        if ($request->hasFile('logo')) {
            Storage::disk('public')->makeDirectory('partners/logos'); // crea si no existe
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public'); // => storage/app/public/partners/logos
        }
        Partner::create($data);
        return redirect()->route('partners.index')->with('success', 'Partner creado.');
    }

    public function edit(Partner $partner)
    {
        return view('partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'nombre_comercial' => 'required|unique:partners,nombre_comercial,' . $partner->id,
            'razon_social' => 'nullable',
            'rfc' => 'nullable',
            'telefono' => 'nullable',
            'correo_contacto' => 'nullable|email',
            'direccion' => 'nullable',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_logo' => 'sometimes|boolean',
            'tipo' => 'required|in:proveedor,asociado,mixto',
            'condiciones_comerciales' => 'nullable',
            'comentarios' => 'nullable',
            'is_active' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['nombre_comercial']);
        unset($data['logo']);
        // borrar logo si lo piden
        if ($request->boolean('remove_logo') && $partner->logo_path) {
            Storage::disk('public')->delete($partner->logo_path);
            $data['logo_path'] = null;
        }

        // reemplazar por uno nuevo si subieron
        if ($request->hasFile('logo')) {
            if ($partner->logo_path) {
                Storage::disk('public')->delete($partner->logo_path);
            }
            Storage::disk('public')->makeDirectory('partners/logos');
            $data['logo_path'] = $request->file('logo')->store('partners/logos', 'public');
        }

        $partner->update($data);

        return redirect()->route('partners.index')->with('success', 'Partner actualizado.');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();
        return redirect()->route('partners.index')->with('success', 'Partner eliminado.');
    }
}