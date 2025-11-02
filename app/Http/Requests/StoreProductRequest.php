<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Partner;
use App\Models\ProductWarehouse;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajusta según tu lógica de autorización
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            // Partner/Proveedor
            'partner_id' => 'required|exists:partners,id',
            
            // Información básica del producto
            'name' => 'required|string|max:255',
            'model_code' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string|max:500',
            
            // Categoría
            'product_category_id' => 'nullable|exists:product_categories,id',
            
            // Imágenes
            'main_image' => 'nullable|url|max:500',
            
            // Flags
            'is_own_product' => 'boolean',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
            
            // Precios (si aplican a nivel producto)
            'base_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
        ];

        // ========================================
        // VALIDACIÓN CONDICIONAL DE WAREHOUSE
        // ========================================
        $partnerId = $this->input('partner_id');
        
        if ($partnerId) {
            $partner = Partner::find($partnerId);
            
            if ($partner && $partner->requiresWarehouses()) {
                // Partner tipo Proveedor o Mixto: almacén OBLIGATORIO
                $rules['warehouse_id'] = 'required|exists:product_warehouses,id';
            } else {
                // Partner tipo Asociado: almacén OPCIONAL (puede ser null)
                $rules['warehouse_id'] = 'nullable|exists:product_warehouses,id';
            }
        } else {
            // Si no hay partner_id, hacerlo nullable para que la validación de partner_id se ejecute primero
            $rules['warehouse_id'] = 'nullable|exists:product_warehouses,id';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator.
     */
    public function messages(): array
    {
        return [
            // Partner
            'partner_id.required' => 'Debe seleccionar un proveedor.',
            'partner_id.exists' => 'El proveedor seleccionado no existe.',
            
            // Warehouse
            'warehouse_id.required' => 'Los partners de tipo Proveedor o Mixto requieren seleccionar un almacén.',
            'warehouse_id.exists' => 'El almacén seleccionado no existe.',
            
            // Producto
            'name.required' => 'El nombre del producto es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'model_code.max' => 'El código del modelo no puede exceder 100 caracteres.',
            'keywords.max' => 'Las palabras clave no pueden exceder 500 caracteres.',
            
            // Categoría
            'product_category_id.exists' => 'La categoría seleccionada no existe.',
            
            // Imágenes
            'main_image.url' => 'La imagen principal debe ser una URL válida.',
            'main_image.max' => 'La URL de la imagen no puede exceder 500 caracteres.',
            
            // Precios
            'base_price.numeric' => 'El precio base debe ser un número.',
            'base_price.min' => 'El precio base no puede ser negativo.',
            'sale_price.numeric' => 'El precio de venta debe ser un número.',
            'sale_price.min' => 'El precio de venta no puede ser negativo.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'partner_id' => 'proveedor',
            'warehouse_id' => 'almacén',
            'name' => 'nombre',
            'model_code' => 'código del modelo',
            'description' => 'descripción',
            'keywords' => 'palabras clave',
            'product_category_id' => 'categoría',
            'main_image' => 'imagen principal',
            'is_own_product' => 'producto propio',
            'is_public' => 'público',
            'is_active' => 'activo',
            'base_price' => 'precio base',
            'sale_price' => 'precio de venta',
        ];
    }

    /**
     * Additional validation after standard rules pass.
     * Verifica que el almacén pertenezca al partner seleccionado.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $partnerId = $this->input('partner_id');
            $warehouseId = $this->input('warehouse_id');

            // Solo validar si ambos existen
            if ($partnerId && $warehouseId) {
                $partner = Partner::find($partnerId);
                
                // Verificar que el almacén pertenece al partner
                $warehouse = ProductWarehouse::where('id', $warehouseId)
                    ->where('partner_id', $partnerId)
                    ->first();

                if (!$warehouse) {
                    $validator->errors()->add(
                        'warehouse_id', 
                        'El almacén seleccionado no pertenece a este proveedor.'
                    );
                }

                // Verificar que el almacén esté activo
                if ($warehouse && !$warehouse->is_active) {
                    $validator->errors()->add(
                        'warehouse_id', 
                        'El almacén seleccionado no está activo.'
                    );
                }
            }

            // Validación adicional: Si es producto propio (is_own_product), verificar que el partner puede crearlos
            if ($this->input('is_own_product') && $partnerId) {
                $partner = Partner::find($partnerId);
                
                if ($partner && !$partner->canCreateOwnProducts()) {
                    $validator->errors()->add(
                        'is_own_product', 
                        'Este proveedor no tiene permisos para crear productos propios. Solo partners de tipo Asociado o Mixto pueden hacerlo.'
                    );
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     * Convierte valores booleanos antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_own_product' => $this->boolean('is_own_product'),
            'is_public' => $this->boolean('is_public'),
            'is_active' => $this->boolean('is_active', true), // true por defecto
        ]);
    }
}