<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Warehouse;

class ProductStock extends Model
{
    use HasFactory;
    

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\ProductWarehouse::class, 'warehouse_id');
    }
}
