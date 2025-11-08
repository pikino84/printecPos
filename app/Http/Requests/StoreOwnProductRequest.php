<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOwnProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // El usuario debe tener permiso para crear productos propios
        return $this->user()->can('create', \App\Models\Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $partnerId = auth()->user()->partner_id;
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
            
            // Categoría (debe ser del mismo partner)
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
            
            // Almacén y stock inicial
            'warehouse_id' => [
                'required',
                'exists:product_warehouses,id',
                Rule::exists('product_warehouses', 'id')->where(function ($query) use ($partnerId) {
                    return $query->where('partner_id', $partnerId)
                                 ->where('is_active', true);
                }),
            ],
            'initial_stock' => 'nullable|integer|min:0',
            
            // SKU (opcional, se genera automático)
            'sku' => [
                'nullable',
                'string',
                'max:100',
                'unique:product_variants,sku',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio no puede ser negativo.',
            'price.max' => 'El precio no puede ser mayor a $999,999.99.',
            
            'product_category_id.required' => 'Debes seleccionar una categoría.',
            'product_category_id.exists' => 'La categoría seleccionada no existe o no pertenece a tu partner.',
            
            'warehouse_id.required' => 'Debes seleccionar un almacén.',
            'warehouse_id.exists' => 'El almacén seleccionado no existe o no pertenece a tu partner.',
            
            'main_image.image' => 'El archivo debe ser una imagen válida.',
            'main_image.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif o webp.',
            'main_image.max' => 'La imagen no puede ser mayor a 5MB.',
            
            'initial_stock.integer' => 'El stock inicial debe ser un número entero.',
            'initial_stock.min' => 'El stock inicial no puede ser negativo.',
            
            'sku.unique' => 'Este SKU ya está en uso. Por favor, elige otro.',
            'sku.max' => 'El SKU no puede tener más de 100 caracteres.',
            
            'unit_package.integer' => 'Las unidades por paquete deben ser un número entero.',
            'unit_package.min' => 'Debe haber al menos 1 unidad por paquete.',
            
            'product_weight.numeric' => 'El peso debe ser un número válido.',
            'product_weight.min' => 'El peso no puede ser negativo.',
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
            'warehouse_id' => 'almacén',
            'initial_stock' => 'stock inicial',
            'main_image' => 'imagen principal',
            'sku' => 'SKU',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir checkboxes a booleanos si no están presentes
        $this->merge([
            'is_active' => $this->has('is_active') ? true : false,
            'featured' => $this->has('featured') ? true : false,
            'is_public' => $this->has('is_public') ? true : false,
        ]);
    }
}