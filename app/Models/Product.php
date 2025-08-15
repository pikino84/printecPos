<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'short_name',
        'description',
        'keywords',
        'material',
        'packing_type',
        'impression_type',
        'unit_package',
        'box_size',
        'box_weight',
        'product_weight',
        'product_size',
        'model_code',
        'product_provider_id',
        'image_path',
        'area_print',
        'partner_id',
        'created_by',
    ];

    public function provider()
    {
        return $this->belongsTo(ProductProvider::class, 'product_provider_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    
    // Visibilidad: Printec (1) + del partner logueado
    public function scopeVisibleFor($q, User $user){
        return $q->whereIn('partner_id', [1, $user->partner_id]);
    }

    // Galería = [main_image, imágenes de variantes]
    public function getGalleryAttribute(): array
    {
        $images = [];
        if ($this->main_image) {
            $images[] = Storage::disk('public')->url($this->main_image);
        }
        foreach ($this->variants as $v) {
            if ($v->image) {
                $images[] = Storage::disk('public')->url($v->image);
            }
        }
        // Quita duplicados por si alguna variante repite archivo
        return array_values(array_unique($images));
    }


    public function categories()
    {
        return $this->hasManyThrough(
            \App\Models\PrintecCategory::class,
            \App\Models\ProductCategory::class,
            'id', // ProductCategory.id
            'id', // PrintecCategory.id
            'product_category_id', // Product.product_category_id (asegúrate de que esto exista o ajústalo)
            'printec_category_id' // ProductCategory.printec_category_id (ajusta si es diferente)
        );
    }

    public function productCategory()
    {
        return $this->belongsTo(\App\Models\ProductCategory::class, 'product_category_id');
    }

    public function stocks()
    {
        return $this->hasManyThrough(
            \App\Models\ProductStock::class,
            \App\Models\ProductVariant::class,
            'product_id',     // Foreign key en variants
            'variant_id',     // Foreign key en stocks
            'id',             // Local key en products
            'id'              // Local key en variants
        );
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}


    
    