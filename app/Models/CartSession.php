<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'variant_id',
        'warehouse_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(ProductWarehouse::class, 'warehouse_id');
    }

    // Accessor para producto
    public function getProductAttribute()
    {
        return $this->variant->product;
    }

    // Scope para obtener carrito del usuario
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Método estático para obtener contador
    public static function getCartCount($userId)
    {
        return self::where('user_id', $userId)->sum('quantity');
    }

    // Método estático para obtener total del carrito
    public static function getCartTotal($userId)
    {
        return self::where('user_id', $userId)
            ->with('variant.product')
            ->get()
            ->sum(function ($item) {
                $price = $item->variant->price ?? $item->variant->product->price;
                return $item->quantity * $price;
            });
    }
}