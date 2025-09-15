<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductStock;
use App\Models\Product;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',           
        'slug',
        'code_name',
        'color_name',
        'image',
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class, 'variant_id');
    }

    public function totalStock()
    {
        return $this->stocks()->sum('stock');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : $this->product->main_image_url;
    }

    public function getTotalStockAttribute()
    {
        return $this->stocks->sum('stock');
    }

    public function getEffectivePriceAttribute()
    {
        return $this->price ?? $this->product->price;
    }

}
