<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOwnProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // El usuario debe poder editar el producto propio
        return $this->user()->can('update', $this->route('own_product'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('own_product')->id;
        $partnerId = $this->route('own_product')->partner_id;

        return [
            // Información básica
            'name' => 'required|string|max:255',
            'model_code' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0|max:999999.99',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            
            // Especificaciones
            'material' => 'nullable|string|max:255',
            'packing_type' => 'nullable|string|max:255',
            'unit_package' => 'nullable|integer|min:1',
            'product_weight' => 'nullable|numeric|min:0',
            'product_size' => 'nullable|string|max:255',
            'area_print' => 'nullable|string|max:255',
            
            // Categoría (del mismo partner)
            'product_category_id' => [
                'required',
                'exists:product_categories,id',
                Rule::exists('product_categories', 'id')->where(function ($query) use ($partnerId) {
                    return $query->where('partner_id', $partnerId);
                }),
            ],
            
            // Estados
            'is_active' => 'boolean',
            'featured' => 'boolean',
            'is_public' => 'boolean',
            
            // Imagen principal
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            
            // Variantes (array opcional)
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.sku' => [
                'required_with:variants',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($productId) {
                    // Extraer el índice de la variante
                    preg_match('/variants\.(\d+)\.sku/', $attribute, $matches);
                    $index = $matches[1] ?? null;
                    
                    if ($index !== null) {
                        $variantId = $this->input("variants.{$index}.id");
                        
                        // Verificar que el SKU sea único
                        $exists = \App\Models\ProductVariant::where('sku', $value)
                            ->where('product_id', '!=', $productId)
                            ->when($variantId, function($query) use ($variantId) {
                                return $query->where('id', '!=', $variantId);
                            })
                            ->exists();
                        
                        if ($exists) {
                            $fail('El SKU ya existe en otro producto.');
                        }
                    }
                },
            ],
            'variants.*.color_name' => 'required_with:variants|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0|max:999999.99',
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            
            // Stock por almacén
            'variants.*.stocks' => 'nullable|array',
            'variants.*.stocks.*' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio no puede ser negativo.',
            'price.max' => 'El precio no puede ser mayor a $999,999.99.',
            
            'product_category_id.required' => 'Debes seleccionar una categoría.',
            'product_category_id.exists' => 'La categoría seleccionada no existe o no pertenece a tu partner.',
            
            'main_image.image' => 'El archivo debe ser una imagen válida.',
            'main_image.mimes' => 'La imagen debe ser: jpeg, png, jpg, gif o webp.',
            'main_image.max' => 'La imagen no puede ser mayor a 5MB.',
            
            'variants.*.sku.required_with' => 'El SKU es obligatorio para cada variante.',
            'variants.*.color_name.required_with' => 'El nombre del color es obligatorio para cada variante.',
            'variants.*.price.numeric' => 'El precio de la variante debe ser un número válido.',
            'variants.*.price.max' => 'El precio de la variante no puede ser mayor a $999,999.99.',
            'variants.*.image.image' => 'El archivo debe ser una imagen válida.',
            'variants.*.image.max' => 'La imagen de la variante no puede ser mayor a 5MB.',
            
            'variants.*.stocks.*.min' => 'El stock no puede ser negativo.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'price' => 'precio',
            'model_code' => 'código del modelo',
            'description' => 'descripción',
            'short_description' => 'descripción corta',
            'material' => 'material',
            'packing_type' => 'tipo de empaque',
            'unit_package' => 'unidades por paquete',
            'product_weight' => 'peso del producto',
            'product_size' => 'tamaño del producto',
            'area_print' => 'área de impresión',
            'product_category_id' => 'categoría',
            'main_image' => 'imagen principal',
        ];
    }
}