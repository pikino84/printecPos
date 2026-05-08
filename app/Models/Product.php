<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'slug',
        'name',
        'short_name',
        'description',
        'keywords',
        'material',
        'packing_type',
        'impression_type',
        'unit_package',
        'box_size',
        'box_weight',
        'product_weight',
        'product_size',
        'model_code',
        'main_image',
        'area_print',
        'partner_id',
        'owner_id',
        'created_by',
        'product_category_id',
        'price',
        'short_description',
        'meta_description',
        'meta_keywords',
        'featured',
        'new',
        'catalog_page',
        'is_active',
        // NUEVOS CAMPOS
        'is_own_product',
        'is_public',
        'unit_type',
    ];

    public const UNIT_TYPE_METRO_CUADRADO = 'metro_cuadrado';

    public const UNIT_TYPE_METRO_LINEAL = 'metro_lineal';

    public const PER_METER_UNIT_TYPES = [
        self::UNIT_TYPE_METRO_CUADRADO,
        self::UNIT_TYPE_METRO_LINEAL,
    ];

    /**
     * Producto se vende por unidad lineal o de área (lonas, viniles).
     * Cuando es true, la cantidad acepta decimales y la UI muestra step="0.01".
     */
    public function isPerMeter(): bool
    {
        return in_array($this->unit_type, self::PER_METER_UNIT_TYPES, true);
    }

    protected $casts = [
        'price' => 'decimal:2',
        'featured' => 'boolean',
        'new' => 'boolean',
        'is_active' => 'boolean',
        'is_own_product' => 'boolean',
        'is_public' => 'boolean',
    ];

    // Activity Log
    protected static $logName = 'producto';

    protected static $logAttributes = ['name', 'price', 'is_active', 'is_own_product', 'is_public'];

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'price', 'is_active', 'is_own_product', 'is_public'])
            ->logOnlyDirty()
            ->useLogName('producto');
    }

    // ========================================================================
    // RELACIONES EXISTENTES
    // ========================================================================

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function owner()
    {
        return $this->belongsTo(Partner::class, 'owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stocks()
    {
        return $this->hasManyThrough(ProductStock::class, ProductVariant::class, 'product_id', 'variant_id');
    }

    public function categories()
    {
        return $this->hasManyThrough(
            PrintecCategory::class,
            ProductCategory::class,
            'id',
            'id',
            'product_category_id',
            'printec_category_id'
        );
    }

    // ========================================================================
    // SCOPES PARA PRODUCTOS PROPIOS
    // ========================================================================

    // Scope para productos propios únicamente
    public function scopeOwnProducts($query)
    {
        return $query->where('is_own_product', true);
    }

    // Scope para productos de proveedores únicamente
    public function scopeProviderProducts($query)
    {
        return $query->where('is_own_product', false);
    }

    // Scope alternativo para visibilidad por partner (más detallado)
    public function scopeVisibleFor($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where(function ($subQuery) use ($user) {
                // Productos propios del partner del usuario
                $subQuery->where('partner_id', $user->partner_id)
                    ->where('is_own_product', true);
            })
                ->orWhere(function ($subQuery) use ($user) {
                    // Productos públicos de Printec (si el usuario no es de Printec)
                    if ($user->partner_id != 1) {
                        $subQuery->where('partner_id', 1)
                            ->where('is_public', true);
                    }
                })
                ->orWhere(function ($subQuery) {
                    // Productos de proveedores (no propios) visibles para todos
                    $subQuery->where('is_own_product', false)
                        ->where('is_active', true);
                });
        });
    }

    // ========================================================================
    // MÉTODOS DE NEGOCIO PARA PRODUCTOS PROPIOS
    // ========================================================================

    public function canBeViewedBy(User $user): bool
    {
        // 🔍 DEBUG
        \Log::info('=== canBeViewedBy DEBUG ===', [
            'product_id' => $this->id,
            'product_partner_id' => $this->partner_id,
            'product_is_own' => $this->is_own_product,
            'product_is_public' => $this->is_public,
            'user_id' => $user->id,
            'user_partner_id' => $user->partner_id,
        ]);

        if (! $this->is_own_product) {
            \Log::info('→ Not own product, returning TRUE');

            return true;
        }

        if ($this->partner_id === $user->partner_id) {
            \Log::info('→ Same partner, returning TRUE');

            return true;
        }

        if ($this->partner_id === 1 && $this->is_public) {
            \Log::info('→ Public Printec product, returning TRUE');

            return true;
        }

        if ($user->partner_id === 1) {
            \Log::info('→ User is Printec, returning TRUE');

            return true;
        }

        \Log::info('→ No condition met, returning FALSE');

        return false;
    }

    public function isOwnProductOf(User $user): bool
    {
        return $this->is_own_product && $this->partner_id === $user->partner_id;
    }

    public function isPrintecPublicProduct(): bool
    {
        return $this->is_own_product && $this->partner_id === 1 && $this->is_public;
    }

    // ========================================================================
    // ACCESSORS EXISTENTES (mantener)
    // ========================================================================

    public function getGalleryAttribute(): array
    {
        $images = [];
        if ($this->main_image) {
            $images[] = Storage::disk('public')->url($this->main_image);
        }
        foreach ($this->variants as $v) {
            if ($v->image) {
                $images[] = Storage::disk('public')->url($v->image);
            }
        }

        return array_values(array_unique($images));
    }

    // Accessors
    public function getMainImageUrlAttribute()
    {
        return $this->main_image ? Storage::url($this->main_image) : null;
    }

    public function getDisplayPriceAttribute()
    {
        return '$'.number_format($this->price, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalStockAttribute()
    {
        return $this->variants->sum(function ($variant) {
            return $variant->stocks->sum('stock');
        });
    }

    // Métodos auxiliares
    public function hasVariants()
    {
        return $this->variants()->count() > 0;
    }

    public function getActiveVariants()
    {
        return $this->variants()->whereHas('stocks', function ($query) {
            $query->where('stock', '>', 0);
        })->get();
    }
}
