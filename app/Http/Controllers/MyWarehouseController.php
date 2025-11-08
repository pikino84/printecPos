<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductWarehouse;
use App\Models\ProductWarehousesCities;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MyWarehouseController extends Controller
{
    /**
     * Mostrar almacenes del partner autenticado
     */
    public function index()
    {
        $user = Auth::user();
        
        // Si es super admin, redirigir al index global
        if ($user->hasRole('super admin')) {
            return redirect()->route('warehouses.index');
        }

        $partner = $user->partner;
        
        // Obtener solo almacenes del partner del usuario
        $warehouses = ProductWarehouse::with(['city'])
            ->where('partner_id', $user->partner_id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('my-warehouses.index', compact('warehouses', 'partner'));
    }

    /**
     * Formulario para crear almacén
     */
    public function create()
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        $cities = ProductWarehousesCities::orderBy('name')->get();
        
        return view('my-warehouses.create', compact('cities', 'partner'));
    }

    /**
     * Guardar nuevo almacén
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $partnerId = $user->partner_id;

        $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9\-]+$/', // Solo minúsculas, números y guiones
                Rule::unique('product_warehouses')->where(function ($query) use ($partnerId) {
                    return $query->where('partner_id', $partnerId);
                }),
            ],
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'city_id' => 'nullable|exists:product_warehouses_cities,id',
            'is_active' => 'nullable|boolean',
        ], [
            'codigo.regex' => 'El código solo puede contener letras minúsculas, números y guiones',
            'codigo.unique' => 'Ya tienes un almacén con ese código',
        ]);

        ProductWarehouse::create([
            'partner_id' => $partnerId, // Automático del usuario
            'codigo' => strtolower($request->codigo),
            'name' => $request->name,
            'nickname' => $request->nickname,
            'city_id' => $request->city_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('my-warehouses.index')
            ->with('success', 'Almacén creado exitosamente.');
    }

    /**
     * Formulario para editar almacén
     */
    public function edit($id)
    {
        $user = Auth::user();
        $partner = $user->partner;
        
        // Verificar que el almacén pertenece al partner del usuario
        $warehouse = ProductWarehouse::where('partner_id', $user->partner_id)
            ->with(['city', 'stocks'])
            ->findOrFail($id);
        
        $cities = ProductWarehousesCities::orderBy('name')->get();
        
        return view('my-warehouses.edit', compact('warehouse', 'cities', 'partner'));
    }

    /**
     * Actualizar almacén
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        // Verificar que el almacén pertenece al partner del usuario
        $warehouse = ProductWarehouse::where('partner_id', $user->partner_id)
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'city_id' => 'nullable|exists:product_warehouses_cities,id',
            'is_active' => 'nullable|boolean',
        ]);

        $warehouse->update([
            'name' => $request->name,
            'nickname' => $request->nickname,
            'city_id' => $request->city_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('my-warehouses.index')
            ->with('success', 'Almacén actualizado exitosamente.');
    }

    /**
     * Eliminar almacén
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        // Verificar que el almacén pertenece al partner del usuario
        $warehouse = ProductWarehouse::where('partner_id', $user->partner_id)
            ->findOrFail($id);
        
        // Verificar si tiene stock asociado
        $hasStock = $warehouse->stocks()->exists();
        
        if ($hasStock) {
            return redirect()
                ->route('my-warehouses.index')
                ->with('error', 'No se puede eliminar el almacén porque tiene productos con inventario asociado.');
        }

        $warehouse->delete();

        return redirect()
            ->route('my-warehouses.index')
            ->with('success', 'Almacén eliminado exitosamente.');
    }
}