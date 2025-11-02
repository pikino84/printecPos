<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWarehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'codigo',
        'name',
        'nickname',
        'city_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function city()
    {
        return $this->belongsTo(ProductWarehousesCities::class, 'city_id');
    }
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function stocks(){ 
        return $this->hasMany(ProductStock::class, 'warehouse_id'); 
    }
    
    // Almacenes del proveedor X (útil al capturar variantes)
    public function scopeForProvider($q, int $partnerId){ 
        return $q->where('partner_id',$partnerId); 
    }
}
