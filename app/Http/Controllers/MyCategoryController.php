<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MyCategoryController extends Controller
{
    /**
     * Mostrar categorías del partner autenticado
     */
    public function index()
    {
        $user = Auth::user();
        
        // Si es super admin, redirigir a un index global (puedes crear uno después)
        if ($user->hasRole('super admin')) {
            // Por ahora redirige al de asociado pero viendo todas
            $partner = null;
            $categories = ProductCategory::with('partner')
                ->orderBy('name')
                ->get();
        } else {
            $partner = $user->partner;
            
            // Obtener solo categorías del partner del usuario
            $categories = ProductCategory::where('partner_id', $user->partner_id)
                ->orderBy('name')
                ->get();
        }
        
        return view('my-categories.index', compact('categories', 'partner'));
    }

    /**
     * Guardar nueva categoría
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $partnerId = $user->partner_id;

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Único por partner
                Rule::unique('product_categories')->where(function ($query) use ($partnerId) {
                    return $query->where('partner_id', $partnerId);
                }),
            ],
            'subcategory' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Ya tienes una categoría con ese nombre.',
        ]);

        ProductCategory::create([
            'name' => $request->name,
            'subcategory' => $request->subcategory,
            'slug' => Str::slug($request->name),
            'partner_id' => $partnerId, // Automático del usuario
            'is_active' => true,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Actualizar categoría
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $partnerId = $user->partner_id;
        
        // Verificar que la categoría pertenece al partner del usuario
        $category = ProductCategory::where('partner_id', $partnerId)
            ->findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories')->where(function ($query) use ($partnerId) {
                    return $query->where('partner_id', $partnerId);
                })->ignore($id),
            ],
            'subcategory' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Ya tienes una categoría con ese nombre.',
        ]);

        $category->update([
            'name' => $request->name,
            'subcategory' => $request->subcategory,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Eliminar categoría
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        // Verificar que la categoría pertenece al partner del usuario
        $category = ProductCategory::where('partner_id', $user->partner_id)
            ->findOrFail($id);
        
        // Verificar si tiene productos asociados
        $hasProducts = \App\Models\Product::where('product_category_id', $category->id)
            ->exists();
        
        if ($hasProducts) {
            return redirect()
                ->back()
                ->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
        }

        $category->delete();

        return redirect()
            ->back()
            ->with('success', 'Categoría eliminada exitosamente.');
    }
}