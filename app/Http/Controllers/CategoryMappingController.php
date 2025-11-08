<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\PrintecCategory;
use Illuminate\Support\Facades\Auth;

class CategoryMappingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userPartnerId = $user->partner_id;

        // Filtrar categorías según el tipo de usuario
        if ($userPartnerId == 1) {
            // Printec ve TODAS las categorías de proveedores
            $categories = ProductCategory::with(['printecCategories', 'partner'])
                ->get()
                ->sortBy('name');
        } else {
            // Asociados/Proveedores solo ven SUS categorías
            $categories = ProductCategory::with(['printecCategories', 'partner'])
                ->where('partner_id', $userPartnerId)
                ->get()
                ->sortBy('name');
        }

        // Todas las categorías internas de Printec (para mapear)
        $printecCategories = PrintecCategory::orderBy('name')->get();

        return view('category-mappings.index', compact('categories', 'printecCategories'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        $user = Auth::user();
        
        // Verificar que el usuario tenga permiso para editar esta categoría
        if ($user->partner_id != 1 && $category->partner_id !== $user->partner_id) {
            abort(403, 'No tienes permiso para editar esta categoría.');
        }

        // Sincronizar las categorías de Printec mapeadas
        $category->printecCategories()->sync($request->input('category_ids', []));

        return redirect()->back()->with('success', 'Categorías actualizadas correctamente.');
    }
}