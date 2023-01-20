<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
