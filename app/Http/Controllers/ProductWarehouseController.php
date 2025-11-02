<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductWarehouse;
use App\Models\ProductWarehousesCities;
use App\Models\Partner;
use Illuminate\Validation\Rule;

class ProductWarehouseController extends Controller
{
    /**
     * Mostrar listado de almacenes
     */
    public function index()
    {
        $warehouses = ProductWarehouse::with(['partner', 'city'])
            ->orderBy('partner_id')
            ->get();
        
        $cities = ProductWarehousesCities::orderBy('name')->get();
        
        return view('warehouses.index', compact('warehouses', 'cities'));
    }

    /**
     * Mostrar formulario para crear almacén
     */
    public function create()
    {
        $partners = Partner::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $cities = ProductWarehousesCities::orderBy('name')->get();
        
        return view('warehouses.create', compact('partners', 'cities'));
    }

    /**
     * Guardar nuevo almacén
     */
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'codigo' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9\-]+$/', // Solo minúsculas, números y guiones
                // ✅ Validar unicidad compuesta correctamente
                Rule::unique('product_warehouses')->where(function ($query) use ($request) {
                    return $query->where('partner_id', $request->partner_id);
                }),
            ],
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'city_id' => 'nullable|exists:product_warehouses_cities,id',
            'is_active' => 'nullable|boolean',
        ], [
            'codigo.regex' => 'El código solo puede contener letras minúsculas, números y guiones',
            'codigo.unique' => 'Este partner ya tiene un almacén con ese código',
        ]);

        $warehouse = ProductWarehouse::create([
            'partner_id' => $request->partner_id,
            'codigo' => strtolower($request->codigo), // Asegurar minúsculas
            'name' => $request->name,
            'nickname' => $request->nickname,
            'city_id' => $request->city_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('warehouses.index')
            ->with('success', "Almacén $warehouse->name creado exitosamente.");
    }

    /**
     * Mostrar formulario para editar almacén
     */
    public function edit($id)
    {
        $warehouse = ProductWarehouse::with(['partner', 'city', 'stocks'])
            ->findOrFail($id);
        
        $cities = ProductWarehousesCities::orderBy('name')->get();
        
        return view('warehouses.edit', compact('warehouse', 'cities'));
    }

    /**
     * Actualizar almacén existente
     */
    public function update(Request $request, $id)
    {
        $warehouse = ProductWarehouse::findOrFail($id);

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
            ->route('warehouses.index')
            ->with('success', "Almacén $warehouse->name actualizado correctamente.");
    }

    /**
     * Eliminar almacén
     */
    public function destroy($id)
    {
        $warehouse = ProductWarehouse::findOrFail($id);
        
        // Verificar si tiene stock asociado
        $hasStock = $warehouse->stocks()->exists();
        
        if ($hasStock) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', 'No se puede eliminar el almacén porque tiene productos con inventario asociado.');
        }

        $warehouseName = $warehouse->name;
        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with('success', "Almacén $warehouseName eliminado correctamente.");
    }
}