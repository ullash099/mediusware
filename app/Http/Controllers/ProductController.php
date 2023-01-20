<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('products.index');
    }

    public function products()
    {
        $src = $_GET['src'] ?? null;
        if (!empty($src)) {
            $datatable = Product::where('name', 'like', '%' . $src . '%')
                ->orWhere('name_l', 'like', '%' . $src . '%')
                ->orWhere('card', 'like', '%' . $src . '%')
                ->orWhere('phone', 'like', '%' . $src . '%')
                ->orWhere('phone_alt', 'like', '%' . $src . '%')
                ->orWhere('email', 'like', '%' . $src . '%')
                ->orWhere('address', 'like', '%' . $src . '%')
                ->withTrashed()->with('customer_type')
                ->latest()->paginate(2);
        } else {
            $datatable = Product::with(['variants' => function ($query) {
                $query->with('variant_one','variant_two','variant_three');
            }])->latest()->paginate(2);
        }
        
        return response()->json([
            'datatable'         =>  $datatable,
            'page'              =>  [
                'theads'                        =>  [
                    (object)[
                        'txt'   =>  '#',
                        'style' =>  ['width'=>'5%']
                    ],
                    (object)[
                        'txt'   =>  __('Title'),
                        'style' =>  ['width'=>'15%']
                    ],
                    (object)[
                        'txt'   =>  __('Description'),
                        'style' =>  ['width'=>'25%'],
                        'class' =>  'text-center'
                    ],
                    (object)[
                        'txt'   =>  __('Varient'),
                        'style' =>  ['width'=>'40%'],
                        'class' =>  'text-center'
                    ],
                    (object)[
                        'txt'   =>  __('Aaction'),
                        'style' =>  ['width'=>'10%'],
                        'class' =>  'text-center'
                    ],
                ]
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
