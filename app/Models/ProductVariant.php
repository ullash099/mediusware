<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'variant', 'variant_id', 'product_id'
    ];

    public function variant_info()
    {
        return $this->belongsTo(Variant::class,'variant_id');
    }
}
