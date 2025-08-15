<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'product_categories';
    
    protected $fillable = [
        'name',
        'subcategory',
        'slug',
        'partner_id',
        'provider_name',
        'provider_slug',

    ];

    // Relación hacia las categorías internas de Printec
    public function printecCategories()
    {
        return $this->belongsToMany(
            \App\Models\PrintecCategory::class,
            'printec_category_product_category',
            'product_category_id',
            'printec_category_id'
        );
    }
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

}
