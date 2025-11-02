<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'is_active',
        'code',
        'description',
        'email',
        'phone',
        'address',
        // Agrega aquí otros campos según tu estructura
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
     * Relación con productos del partner
     */
    public function products()
    {
        return $this->hasMany(PartnerProduct::class);
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
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
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
}