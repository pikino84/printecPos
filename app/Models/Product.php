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
    ];

    public function provider()
    {
        return $this->belongsTo(ProductProvider::class, 'product_provider_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
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


    public function images()
    {
        return $this->hasMany(\App\Models\ProductImage::class);
    }

    public function productCategory()
    {
        return $this->belongsTo(\App\Models\ProductCategory::class, 'product_category_id');
    }

}


    
    