<?php

namespace App\Models;

use App\Models\PartnerPricing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'contact_name',
        'contact_phone',
        'contact_email',
        'direccion',
        'type',
        'commercial_terms',
        'comments',
        'is_active',
        'default_entity_id',
        'api_key',
        'api_show_prices',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'api_show_prices' => 'boolean',
    ];

    // ========================================================================
    // RELACIONES
    // ========================================================================
    
    /**
     * Relación con usuarios
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relación con clientes (muchos a muchos)
     */
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_partner')
            ->withPivot(['first_contact_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Relación con entidades del partner
     */
    public function entities()
    {
        return $this->hasMany(PartnerEntity::class);
    }
    /**
     * Relación con la entidad predeterminada del partner
     */
    public function defaultEntity()
    {
        return $this->belongsTo(PartnerEntity::class, 'default_entity_id');
    }

    /**
     * Relación con productos del partner
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relación con almacenes (warehouses)
     */
    public function warehouses()
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    /**
     * Relación con cotizaciones
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Relación con configuración de precios del partner
     */
    public function pricing()
    {
        return $this->hasOne(PartnerPricing::class);
    }

    // ========================================================================
    // SCOPES
    // ========================================================================
    
    /**
     * Scope para obtener solo partners activos
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para obtener solo partners inactivos
     */
    public function scopeInactive(Builder $query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope para obtener solo partners de tipo Asociado y Mixto
     */
    public function scopeAsociadosYMixtos(Builder $query)
    {
        return $query->whereIn('type', ['Asociado', 'Mixto']);
    }

    /**
     * Scope para obtener solo partners de tipo Asociado
     */
    public function scopeAsociados(Builder $query)
    {
        return $query->where('type', 'Asociado');
    }

    /**
     * Scope para obtener solo partners de tipo Mixto
     */
    public function scopeMixtos(Builder $query)
    {
        return $query->where('type', 'Mixto');
    }

    /**
     * Scope para obtener solo partners de tipo Proveedor
     */
    public function scopeProveedores(Builder $query)
    {
        return $query->where('type', 'Proveedor');
    }

    /**
     * Scope para buscar partners por nombre
     */
    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('contact_name', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para ordenar por nombre
     */
    public function scopeOrderByName(Builder $query, $direction = 'asc')
    {
        return $query->orderBy('name', $direction);
    }

    // ========================================================================
    // ACCESSORS Y MUTATORS
    // ========================================================================
    
    /**
     * Obtener el badge de tipo con color
     */
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'Asociado' => '<span class="badge bg-primary">Asociado</span>',
            'Mixto' => '<span class="badge bg-info">Mixto</span>',
            'Proveedor' => '<span class="badge bg-secondary">Proveedor</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge bg-secondary">' . $this->type . '</span>';
    }

    /**
     * Obtener el badge de estado
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active 
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-danger">Inactivo</span>';
    }

    // ========================================================================
    // MÉTODOS AUXILIARES
    // ========================================================================
    
    /**
     * Verificar si el partner es de tipo Asociado
     */
    public function isAsociado()
    {
        return $this->type === 'Asociado';
    }

    /**
     * Verificar si el partner es de tipo Mixto
     */
    public function isMixto()
    {
        return $this->type === 'Mixto';
    }

    /**
     * Verificar si el partner es de tipo Proveedor
     */
    public function isProveedor()
    {
        return $this->type === 'Proveedor';
    }

    /**
     * Verificar si el partner es Asociado o Mixto
     */
    public function isAsociadoOMixto()
    {
        return in_array($this->type, ['Asociado', 'Mixto']);
    }

    /**
     * Verificar si el partner puede vender
     */
    public function canSell()
    {
        return $this->isAsociadoOMixto() && $this->is_active;
    }

    /**
     * Verificar si el partner puede proveer productos
     */
    public function canSupply()
    {
        return in_array($this->type, ['Proveedor', 'Mixto']) && $this->is_active;
    }

    /**
     * Obtener el conteo de usuarios
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Obtener el conteo de clientes
     */
    public function getClientsCountAttribute()
    {
        return $this->clients()->count();
    }

    /**
     * Obtener el conteo de productos
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Obtener el conteo de entidades
     */
    public function getEntitiesCountAttribute()
    {
        return $this->entities()->count();
    }

    // ========================================================================
    // MÉTODOS DE ESTADÍSTICAS
    // ========================================================================
    
    /**
     * Obtener estadísticas del partner
     */
    public function getStats()
    {
        return [
            'users' => $this->users()->count(),
            'clients' => $this->clients()->count(),
            'products' => $this->products()->count(),
            'entities' => $this->entities()->count(),
            'warehouses' => $this->warehouses()->count(),
            'quotes' => $this->quotes()->count(),
            'active_users' => $this->users()->where('is_active', true)->count(),
        ];
    }

    /**
     * Verificar si el partner tiene datos asociados
     */
    public function hasRelatedData()
    {
        return $this->users()->exists() 
            || $this->clients()->exists() 
            || $this->products()->exists()
            || $this->quotes()->exists();
    }

    /**
     * Obtener el total de ventas (si aplica)
     */
    public function getTotalSales()
    {
        return $this->quotes()
            ->where('status', 'approved')
            ->sum('total');
    }

    /**
     * Verificar si el partner requiere almacenes
     */
    public function requiresWarehouses()
    {
        return in_array($this->type, ['Proveedor', 'Mixto']);
    }

    /**
     * Verificar si puede crear productos propios
     */
    public function canCreateOwnProducts()
    {
        return in_array($this->type, ['Asociado', 'Mixto']);
    }

    /**
     * Obtener etiqueta del tipo
     */
    public function getTypeLabel()
    {
        $labels = [
            'Proveedor' => 'Proveedor',
            'Asociado' => 'Asociado',
            'Mixto' => 'Mixto'
        ];
        
        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Obtener descripción del tipo
     */
    public function getTypeDescription()
    {
        $descriptions = [
            'Proveedor' => 'Solo provee productos, requiere almacén',
            'Asociado' => 'Vende productos, no requiere almacén',
            'Mixto' => 'Provee y vende, requiere almacén'
        ];

        return $descriptions[$this->type] ?? '';
    }

    /**
     * Generar nueva API key para el partner
     */
    public function generateApiKey(): string
    {
        $apiKey = bin2hex(random_bytes(32));
        $this->update(['api_key' => $apiKey]);
        return $apiKey;
    }

    /**
     * Revocar API key del partner
     */
    public function revokeApiKey(): void
    {
        $this->update(['api_key' => null]);
    }

    /**
     * Obtener o crear la configuración de pricing
     */
    public function getPricingConfig()
    {
        // Asegurarnos de que el partner tiene un ID
        if (!$this->id) {
            throw new \Exception('El partner debe estar guardado antes de obtener su configuración de pricing');
        }
        
        // Buscar o crear usando el método estático directamente
        $pricing = PartnerPricing::where('partner_id', $this->id)->first();
        
        if (!$pricing) {
            $pricing = PartnerPricing::create([
                'partner_id' => $this->id,
                'markup_percentage' => 0,
                'current_tier_id' => null,
                'last_month_purchases' => 0,
                'current_month_purchases' => 0,
                'manual_tier_override' => false,
            ]);
        }
        
        return $pricing;
    }

    /**
     * Calcular precio final para un producto
     */
    public function calculateProductPrice($basePrice, $isOwnProduct = false)
    {
        $pricing = $this->getPricingConfig();
        
        // isPrintecProduct = true cuando NO es producto propio del partner
        return $pricing->calculatePrice($basePrice, !$isOwnProduct);
    }
}