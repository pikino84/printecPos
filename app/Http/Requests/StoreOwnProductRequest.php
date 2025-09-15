<?php

// app/Http/Requests/StoreOwnProductRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOwnProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el Policy
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'model_code' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:50', // Para variante principal
            'price' => 'required|numeric|min:0|max:999999.99',
            'description' => 'nullable|string|max:5000',
            'short_description' => 'nullable|string|max:500',
            'material' => 'nullable|string|max:255',
            'packing_type' => 'nullable|string|max:255',
            'unit_package' => 'nullable|string|max:255',
            'product_weight' => 'nullable|string|max:255',
            'product_size' => 'nullable|string|max:255',
            'area_print' => 'nullable|string|max:255',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'featured' => 'boolean',
            'new' => 'boolean',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'warehouse_id' => 'nullable|exists:product_warehouses,id',
            'initial_stock' => 'nullable|integer|min:0',
            // Para variantes múltiples
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required_with:variants|string|max:50',
            'variants.*.color_name' => 'nullable|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stocks' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'main_image.image' => 'El archivo debe ser una imagen.',
            'main_image.max' => 'La imagen no puede pesar más de 5MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sku')) {
            $this->merge([
                'sku' => strtoupper(trim($this->sku))
            ]);
        }
    }
}