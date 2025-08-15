<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductWarehousesCities;
use Illuminate\Support\Str;

class ProductWarehousesCitiesController extends Controller
{
    public function index()
    {
        $cities = ProductWarehousesCities::all()->sortBy('name');
        return view('printec-cities.index', compact('cities'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'name' => 'required|string|max:100|unique:product_warehouses_cities,name',
        ]);

        ProductWarehousesCities::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->back()->with('success', 'Ciudad creada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:product_warehouses_cities,name,' . $id,
        ]);

        $city = ProductWarehousesCities::findOrFail($id);
        $city->name = $request->name;
        $city->slug = \Str::slug($request->name); // ✅ Esto genera el nuevo slug
        $city->save();

        return redirect()->back()->with('success', 'Ciudad actualizada.');
    }



    public function destroy($id)
    {
        $city = ProductWarehousesCities::findOrFail($id);
        $city->delete();

        return redirect()->back()->with('success', 'Ciudad eliminada.');
    }
}
