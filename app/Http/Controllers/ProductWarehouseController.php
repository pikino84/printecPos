<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductWarehouse;

class ProductWarehouseController extends Controller
{
    public function index()
    {
        $warehouses = ProductWarehouse::with('provider')->orderBy('provider_id')->get();
        return view('warehouses.index', compact('warehouses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nickname' => 'required|string|max:100',
        ]);

        $warehouse = ProductWarehouse::findOrFail($id);
        $warehouse->nickname = $request->nickname;
        $warehouse->save();

        return redirect()->back()->with('success', 'Nickname actualizado correctamente.');
    }
}
