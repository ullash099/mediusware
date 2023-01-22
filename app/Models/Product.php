<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class,'product_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class,'product_id');
    }

    public function variant_prices()
    {
        return $this->hasMany(ProductVariantPrice::class,'product_id');
    }

    /* public function variants()
    {
        return $this->hasMany(ProductVariantPrice::class,'product_id');
    } */

}
