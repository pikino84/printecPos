<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',               // Nombre comercial del partner
        'slug',               // Slug para URL amigable
        'contact_name',       // Persona de contacto
        'contact_phone',      // Celular
        'contact_email',      // Correo
        'direccion',          // Dirección
        'type',               // Proveedor | Asociado | Mixto
        'commercial_terms',   // Condiciones comerciales
        'comments',           // Comentarios
        'is_active',          // Activo
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function entities()
    {
        return $this->hasMany(\App\Models\PartnerEntity::class);
    }

    public function defaultEntity()
    {
        return $this->hasOne(\App\Models\PartnerEntity::class)->where('is_default', true);
    }

    public function getDefaultEntityAttribute()
    {
        return $this->entities()->where('is_default', true)->first()
            ?: $this->entities()->first();
    }
}
