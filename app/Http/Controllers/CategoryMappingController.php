<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\PrintecCategory;

class CategoryMappingController extends Controller
{
    public function index()
    {
        // Trae todas las categorías externas con sus relaciones actuales
        $categories = ProductCategory::with(['printecCategories', 'productProvider'])->get()->sortBy('name');
        $printecCategories = PrintecCategory::orderBy('name')->get();

        return view('category-mappings.index', compact('categories', 'printecCategories'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        $category->printecCategories()->sync($request->input('category_ids', []));

        return redirect()->back()->with('success', 'Categorías actualizadas correctamente.');
    }
}
