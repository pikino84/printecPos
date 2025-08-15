<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWarehousesCities extends Model
{
    use HasFactory;

    protected $table = 'product_warehouses_cities';
    
    protected $fillable = [
        'name',
        'slug',
    ];

    public function warehouses(){ return $this->hasMany(ProductWarehouse::class, 'city_id'); }
}
