<?php

namespace App\Http\Controllers;

use App\Models\Asociado;
use Illuminate\Http\Request;

class AsociadoController extends Controller
{
    public function index()
    {
        $asociados = Asociado::all();
        //dd($asociados); // Debugging line to check the data
        return view('asociados.index', compact('asociados'));
    }

    public function create()
    {
        return view('asociados.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'telefono' => 'nullable|string|max:20',
            'correo_contacto' => 'nullable|email',
            'direccion' => 'nullable|string',
        ]);

        Asociado::create($data);

        return redirect()->route('asociados.index')->with('success', 'Asociado creado correctamente.');
    }

    public function edit(Asociado $asociado)
    {
        return view('asociados.edit', compact('asociado'));
    }

    public function update(Request $request, Asociado $asociado)
    {
        $data = $request->validate([
            'nombre_comercial' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'telefono' => 'nullable|string|max:20',
            'correo_contacto' => 'nullable|email',
            'direccion' => 'nullable|string',
        ]);

        $asociado->update($data);

        return redirect()->route('asociados.index')->with('success', 'Asociado actualizado.');
    }

    public function destroy(Asociado $asociado)
    {
        $asociado->delete();
        return redirect()->route('asociados.index')->with('success', 'Asociado eliminado.');
    }
}
