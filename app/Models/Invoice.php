<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'partner_entity_id',
        'invoice_number',
        'uuid',
        'series',
        'folio',
        'cfdi_type',
        'payment_form',
        'payment_method',
        'cfdi_use',
        'receptor_rfc',
        'receptor_name',
        'receptor_fiscal_regime',
        'receptor_zip_code',
        'receptor_email',
        'subtotal',
        'tax_rate',
        'tax',
        'total',
        'currency',
        'exchange_rate',
        'status',
        'xml_content',
        'pdf_path',
        'stamped_at',
        'cancelled_at',
        'cancellation_reason',
        'replacement_uuid',
        'payment_number',
        'total_payments',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'stamped_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'folio' => 'integer',
        'payment_number' => 'integer',
        'total_payments' => 'integer',
    ];

    // =========================================================================
    // RELACIONES
    // =========================================================================

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function partnerEntity(): BelongsTo
    {
        return $this->belongsTo(PartnerEntity::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeStamped($query)
    {
        return $query->where('status', 'stamped');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForPartnerEntity($query, $partnerEntityId)
    {
        return $query->where('partner_entity_id', $partnerEntityId);
    }

    // =========================================================================
    // MÉTODOS DE ESTADO
    // =========================================================================

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isStamped(): bool
    {
        return $this->status === 'stamped';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isPendingCancel(): bool
    {
        return $this->status === 'pending_cancel';
    }

    public function canBeStamped(): bool
    {
        return $this->isDraft() && $this->items()->count() > 0;
    }

    public function canBeCancelled(): bool
    {
        return $this->isStamped() && !empty($this->uuid);
    }

    // =========================================================================
    // GENERACIÓN DE NÚMEROS
    // =========================================================================

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";

        $lastInvoice = static::where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('invoice_number')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // CÁLCULOS
    // =========================================================================

    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $tax = $this->items()->sum('tax_amount');

        $this->subtotal = $subtotal;
        $this->tax = $tax;
        $this->total = $subtotal + $tax;
    }

    // =========================================================================
    // CREACIÓN DESDE COTIZACIÓN
    // =========================================================================

    public static function createFromQuote(
        Quote $quote,
        PartnerEntity $partnerEntity,
        int $paymentNumber = 1,
        int $totalPayments = 1,
        float $percentage = 100.0
    ): self {
        // Obtener serie y folio
        $series = $partnerEntity->invoice_series;
        $folio = $partnerEntity->invoice_next_folio;

        // Crear la factura
        $invoice = static::create([
            'quote_id' => $quote->id,
            'partner_entity_id' => $partnerEntity->id,
            'invoice_number' => static::generateInvoiceNumber(),
            'series' => $series,
            'folio' => $folio,
            'cfdi_type' => 'I', // Ingreso
            'payment_form' => '99', // Por definir
            'payment_method' => 'PUE', // Pago en una exhibición
            'cfdi_use' => 'G03', // Gastos en general
            'receptor_rfc' => $quote->client_rfc ?? 'XAXX010101000', // RFC genérico si no hay
            'receptor_name' => $quote->client_razon_social ?? $quote->client_name ?? 'PUBLICO EN GENERAL',
            'receptor_email' => $quote->client_email,
            'subtotal' => 0,
            'tax_rate' => 16.00,
            'tax' => 0,
            'total' => 0,
            'payment_number' => $paymentNumber,
            'total_payments' => $totalPayments,
            'status' => 'draft',
        ]);

        // Incrementar el folio para la siguiente factura
        $partnerEntity->increment('invoice_next_folio');

        // Crear los items de la factura
        $factor = $percentage / 100;
        foreach ($quote->items as $quoteItem) {
            $quantity = $quoteItem->quantity * $factor;
            $subtotal = $quoteItem->unit_price * $quantity;
            $taxAmount = $subtotal * 0.16; // IVA 16%

            $invoice->items()->create([
                'product_key' => '01010101', // Clave genérica
                'unit_key' => 'E48', // Unidad de servicio
                'unit_name' => 'Servicio',
                'sku' => $quoteItem->variant?->sku,
                'description' => $quoteItem->variant?->product?->name ?? 'Producto',
                'quantity' => $quantity,
                'unit_price' => $quoteItem->unit_price,
                'subtotal' => $subtotal,
                'tax_rate' => 16.00,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'quote_item_id' => $quoteItem->id,
            ]);
        }

        // Calcular totales
        $invoice->calculateTotals();
        $invoice->save();

        return $invoice;
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => '<span class="badge badge-secondary">Borrador</span>',
            'stamped' => '<span class="badge badge-success">Timbrada</span>',
            'cancelled' => '<span class="badge badge-danger">Cancelada</span>',
            'pending_cancel' => '<span class="badge badge-warning">Cancelación Pendiente</span>',
            default => '<span class="badge badge-light">Desconocido</span>',
        };
    }

    public function getCfdiTypeLabelAttribute(): string
    {
        return match ($this->cfdi_type) {
            'I' => 'Ingreso',
            'E' => 'Egreso',
            'P' => 'Pago',
            'N' => 'Nómina',
            'T' => 'Traslado',
            default => 'Desconocido',
        };
    }

    public function getFullFolioAttribute(): string
    {
        return "{$this->series}-{$this->folio}";
    }
}
