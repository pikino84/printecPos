<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_comercial',
        'slug',
        'razon_social',
        'rfc',
        'telefono',
        'correo_contacto',
        'direccion',
        'logo',
        'tipo',
        'condiciones_comerciales',
        'comentarios',
        'is_active',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    protected static function booted() {
        static::deleting(function ($partner) {
            if ($partner->logo_path) Storage::disk('public')->delete($partner->logo_path);
        });
    }
    public function entities(){ 
        return $this->hasMany(\App\Models\PartnerEntity::class); 
    }
    public function defaultEntity() { 
        return $this->hasOne(\App\Models\PartnerEntity::class)->where('is_default', true); 
    }
    public function getDefaultEntityAttribute()
    {
        return $this->entities()->where('is_default', true)->first()
            ?: $this->entities()->first();
    }
}