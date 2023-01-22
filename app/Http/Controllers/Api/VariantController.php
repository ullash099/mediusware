<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;

class VariantController extends Controller
{
    /**
     * Get variantions
     */
    public function index()
    {
        $variants = Variant::all();
        return response()->json($variants);
    }
    
    public function product($id)
    {
        $variants = Variant::all();

        $productInfo = Product::with('images')
        ->with(['variant_prices' => function($vpq){
            $vpq->with('variant_one','variant_two','variant_three');
        }])
        ->with(['variants' => function($vq){
            $vq->with('variant_info');
        }])
        ->where('id',$id)->first();
        return response()->json([
            'variants'      =>  $variants,
            'productInfo'   =>  $productInfo,
        ]);
    }
}
