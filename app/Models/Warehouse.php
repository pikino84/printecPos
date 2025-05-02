<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'alias',
        'codigo',
        'slug',
        'ubicacion',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }
}
