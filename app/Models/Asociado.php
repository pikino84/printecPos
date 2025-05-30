<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asociado extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'rfc',
        'telefono',
        'correo_contacto',
        'direccion',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
