<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Warehouse;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'warehouse_id', 
        'stock'
    ];
    
    protected $casts = [
        'stock' => 'integer'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\ProductWarehouse::class, 'warehouse_id');
    }
}
