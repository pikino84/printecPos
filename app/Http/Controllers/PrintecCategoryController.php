<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrintecCategory;
use Illuminate\Support\Str;

class PrintecCategoryController extends Controller
{
    public function index()
    {
        $categories = PrintecCategory::all()->sortBy('name');
        return view('printec-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:printec_categories,name',
        ]);

        PrintecCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->back()->with('success', 'Categoría creada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:printec_categories,name,' . $id,
        ]);

        $category = PrintecCategory::findOrFail($id);
        $category->name = $request->name;
        $category->slug = \Str::slug($request->name); // ✅ Esto genera el nuevo slug
        $category->save();

        return redirect()->back()->with('success', 'Categoría actualizada.');
    }



    public function destroy($id)
    {
        $category = PrintecCategory::findOrFail($id);
        $category->delete();

        return redirect()->back()->with('success', 'Categoría eliminada.');
    }
}
