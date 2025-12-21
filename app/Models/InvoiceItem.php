<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_key',
        'unit_key',
        'unit_name',
        'sku',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'quote_item_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function quoteItem(): BelongsTo
    {
        return $this->belongsTo(QuoteItem::class);
    }

    // =========================================================================
    // EVENTOS DEL MODELO
    // =========================================================================

    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item) {
            // Auto-calcular subtotal si no está definido
            if (empty($item->subtotal)) {
                $item->subtotal = ($item->quantity * $item->unit_price) - $item->discount;
            }

            // Auto-calcular impuesto si no está definido
            if (empty($item->tax_amount) && $item->tax_rate > 0) {
                $item->tax_amount = $item->subtotal * ($item->tax_rate / 100);
            }

            // Auto-calcular total
            if (empty($item->total)) {
                $item->total = $item->subtotal + $item->tax_amount;
            }
        });
    }

    // =========================================================================
    // MÉTODOS DE CÁLCULO
    // =========================================================================

    public function recalculate(): void
    {
        $this->subtotal = ($this->quantity * $this->unit_price) - $this->discount;
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        $this->total = $this->subtotal + $this->tax_amount;
    }
}
