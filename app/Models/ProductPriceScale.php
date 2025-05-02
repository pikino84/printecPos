<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceScale extends Model
{
    protected $fillable = [
        'product_id',
        'scale',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
