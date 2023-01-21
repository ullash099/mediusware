<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariantPrice;

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
        $title = $_GET['title'] ?? null;
        $variant = $_GET['variant'] ?? null;
        $min_price = $_GET['min_price'] ?? null;
        $max_price = $_GET['max_price'] ?? null;
        $date = $_GET['date'] ?? null;

        if (!empty($title) || !empty($variant) || !empty($min_price) || !empty($max_price) || !empty($date)) {
            $variantList = [];
            if (!empty($variant)) {
                $variantList = Product::select('product_variants.id')
                ->join('product_variants','product_variants.product_id','=','products.id')
                ->where(function($vq) use ($title, $variant, $date){
                    $vq->where('variant_id',$variant);
                    if (!empty($title) && !empty($date)) {
                        $vq->where('products.title', 'like', '%' . $title . '%')
                        ->orwhereDate('products.created_at', '=', date('Y-m-d',strtotime($date)));
                    }
                    elseif (!empty($title)) {
                        $vq->where('products.title', 'like', '%' . $title . '%');
                    }
                    elseif (!empty($date)) {
                        $vq->whereDate('products.created_at', '=', date('Y-m-d',strtotime($date)));
                    }
                })
                ->get();
            }
            $datatable = Product::where(function($q) use ($title, $date) {
                if (!empty($title) && !empty($date)) {
                    $q->where('title', 'like', '%' . $title . '%')
                    ->orwhereDate('created_at', '=', date('Y-m-d',strtotime($date)));
                }
                elseif (!empty($title)) {
                    $q->where('title', 'like', '%' . $title . '%');
                }
                elseif (!empty($date)) {
                    $q->whereDate('created_at', '=', date('Y-m-d',strtotime($date)));
                }
            })
            ->with(['variants' => function ($query) use ($variantList,$min_price,$max_price) {
                if (!empty($variantList)){
                    $query->whereIn('product_variant_one',$variantList)
                    ->orWhereIn('product_variant_two',$variantList)
                    ->orWhereIn('product_variant_two',$variantList);
                }
                if (!empty($min_price) && !empty($max_price)){
                    $query->whereBetween('price',[$min_price,$max_price]);
                }
                $query->with('variant_one','variant_two','variant_three');
            }])->latest('created_at')->paginate(2);
        } else {
            $datatable = Product::with(['variants' => function ($query) {
                $query->with('variant_one','variant_two','variant_three');
            }])->latest()->paginate(2);
        }
        
        return response()->json([
            'datatable'         =>  $datatable,
            'variants'          =>  Variant::all(),
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
