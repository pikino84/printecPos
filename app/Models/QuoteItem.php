<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'variant_id',
        'warehouse_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relaciones
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(ProductWarehouse::class, 'warehouse_id');
    }

    // Accesor para obtener el producto
    public function getProductAttribute()
    {
        return $this->variant->product;
    }

    // Calcular subtotal automáticamente
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->unit_price;
        });
    }
}