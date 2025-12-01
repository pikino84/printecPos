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
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    // ========================================================================
    // RELACIONES
    // ========================================================================

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

    // ========================================================================
    // ACCESSORS
    // ========================================================================

    /**
     * Obtener el producto a través de la variante
     */
    public function getProductAttribute()
    {
        return $this->variant->product;
    }

    /**
     * Obtener el precio efectivo (guardado o de la variante)
     */
    public function getEffectivePriceAttribute()
    {
        // Si tiene precio guardado, usarlo
        if ($this->unit_price !== null) {
            return (float) $this->unit_price;
        }

        // Fallback: precio de la variante o producto
        return (float) ($this->variant->price ?? $this->variant->product->price ?? 0);
    }

    /**
     * Obtener el total del item (precio × cantidad)
     */
    public function getItemTotalAttribute()
    {
        return $this->effective_price * $this->quantity;
    }

    // ========================================================================
    // SCOPES
    // ========================================================================

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ========================================================================
    // MÉTODOS ESTÁTICOS
    // ========================================================================

    /**
     * Obtener contador de items en el carrito
     */
    public static function getCartCount($userId)
    {
        return self::where('user_id', $userId)->sum('quantity');
    }

    /**
     * Obtener total del carrito usando el precio guardado
     */
    public static function getCartTotal($userId)
    {
        return self::where('user_id', $userId)
            ->with('variant.product')
            ->get()
            ->sum(function ($item) {
                return $item->item_total;
            });
    }

    /**
     * Obtener items del carrito con totales
     */
    public static function getCartItems($userId)
    {
        return self::where('user_id', $userId)
            ->with(['variant.product.productCategory', 'variant.stocks.warehouse', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}