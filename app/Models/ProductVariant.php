<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductStock;
use App\Models\Product;

class ProductVariant extends Model
{
    use HasFactory;
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stock()
    {
        return $this->hasOne(ProductStock::class, 'variant_id');
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class, 'variant_id');
    }

    public function totalStock()
    {
        return $this->stocks()->sum('stock');
    }

}
