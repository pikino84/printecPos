<?php
// app/Http/Requests/UpdateOwnProductRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOwnProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            // ... reglas básicas ...
            
            // Validación de variantes con reglas personalizadas
            'variants' => 'nullable|array|min:1',
            'variants.*.sku' => [
                'required_with:variants',
                'string',
                'max:100',
                Rule::unique('own_product_variants', 'sku')
                    ->ignore($this->variants[$key]['id'] ?? null)
                    ->where(function ($query) {
                        return $query->where('own_product_id', '!=', $this->route('own_product')->id);
                    })
            ],
            'variants.*.color_name' => 'nullable|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0|lt:999999.99',
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar SKUs únicos dentro del mismo producto
            $variants = $this->input('variants', []);
            $skus = array_column($variants, 'sku');
            
            if (count($skus) !== count(array_unique($skus))) {
                $validator->errors()->add('variants', 'No puede haber SKUs duplicados en el mismo producto.');
            }
            
            // Validar que al menos una variante tenga stock si el producto está activo
            if ($this->input('is_active')) {
                $hasStock = false;
                foreach ($variants as $variant) {
                    if (isset($variant['stocks']) && array_sum($variant['stocks']) > 0) {
                        $hasStock = true;
                        break;
                    }
                }
                
                if (!$hasStock && count($variants) > 0) {
                    $validator->errors()->add('variants', 'Un producto activo debe tener al menos stock en una variante.');
                }
            }
        });
    }
    
    public function messages()
    {
        return [
            'variants.*.sku.required_with' => 'El SKU es obligatorio para cada variante.',
            'variants.*.sku.unique' => 'Este SKU ya existe en otro producto.',
            'variants.*.price.lt' => 'El precio no puede ser mayor a $999,999.99',
            'variants.*.image.image' => 'El archivo debe ser una imagen válida.',
            'variants.*.image.max' => 'La imagen no puede ser mayor a 5MB.',
            'variants.*.stocks.*.min' => 'El stock no puede ser negativo.',
        ];
    }
}

// Método helper en el modelo OwnProduct para obtener stock total
class OwnProduct extends Model
{
    public function getTotalStockAttribute()
    {
        return $this->variants->sum(function($variant) {
            return $variant->stocks->sum('stock');
        });
    }
    
    public function getHasVariantsAttribute()
    {
        return $this->variants()->count() > 0;
    }
    
    public function getActiveVariantsAttribute()
    {
        return $this->variants()->whereHas('stocks', function($query) {
            $query->where('stock', '>', 0);
        })->get();
    }
}

// Observer para manejar eventos de variantes
class OwnProductVariantObserver
{
    public function creating(OwnProductVariant $variant)
    {
        // Generar SKU automáticamente si no se proporciona
        if (empty($variant->sku)) {
            $baseCode = $variant->ownProduct->model_code ?: 'PROD';
            $colorCode = substr($variant->color_name ?: 'VAR', 0, 3);
            $variant->sku = strtoupper("{$baseCode}-{$colorCode}");
        }
    }
    
    public function deleting(OwnProductVariant $variant)
    {
        // Eliminar imagen asociada
        if ($variant->image_path) {
            Storage::delete('public/' . $variant->image_path);
        }
        
        // Eliminar stocks asociados
        $variant->stocks()->delete();
    }
}