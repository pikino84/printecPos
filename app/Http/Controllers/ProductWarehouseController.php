<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductWarehouse;
use App\Models\ProductWarehousesCities;

class ProductWarehouseController extends Controller
{
    public function index()
    {
        $warehouses = ProductWarehouse::with('partner')->orderBy('partner_id')->get();
        $cities = ProductWarehousesCities::all();
        return view('warehouses.index', compact('warehouses', 'cities'));
    }

    public function update(Request $request, $id)
    {
        
        $warehouse = ProductWarehouse::findOrFail($id);
        $warehouse->nickname = $request->nickname;
        //actualizar la ciudad si se envió
        if ($request->has('city_id')) {
            $warehouse->city_id = $request->city_id;
        } else {
            $warehouse->city_id = null; // Si no se envió, establecer como null
        }
        $warehouse->save();

        return redirect()->back()->with('success', 'Nickname actualizado correctamente.');
    }
}
