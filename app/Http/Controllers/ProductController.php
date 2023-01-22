<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariantPrice;

use function GuzzleHttp\Promise\all;
use Illuminate\Support\Facades\Validator;

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
            #DB::enableQueryLog();
            $variantList = [];
            if (!empty($variant)) {
                $variantList = Product::select('product_variants.id')
                ->join('product_variants','product_variants.product_id','=','products.id')
                ->where(function($vq) use ($title, $variant, $date){
                    $vq->where('variant',$variant);
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
            $datatable = Product::where(function($q) use ($title, $date,$variantList) {
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
            ->with(['variant_prices' => function ($query) use ($variantList,$min_price,$max_price) {
                if (!empty($variantList)){
                    $query->where(function($vq) use ($variantList){
                        $vq->whereIn('product_variant_one',$variantList)
                        ->orWhereIn('product_variant_two',$variantList)
                        ->orWhereIn('product_variant_two',$variantList);
                    });
                }
                if (!empty($min_price) && !empty($max_price)){
                    $query->whereBetween('price',[$min_price,$max_price]);
                }
                $query->with('variant_one','variant_two','variant_three');
            }])->latest('created_at')->paginate(2);

            #dd(DB::getQueryLog());
        } else {
            $datatable = Product::with(['variant_prices' => function($vpq){
                $vpq->with('variant_one','variant_two','variant_three');
            }])->latest()->paginate(2);
        }

        $variants = [];
        $variantsInfo = Variant::all();
        foreach ($variantsInfo as $var) {
            $sub = [];

            $product_variants = DB::table('product_variants')
            ->select('variant')
            ->where('variant_id',$var->id)
            ->groupBy('variant')->get();
            
            foreach ($product_variants as $pv) {
                array_push($sub,(object)[
                    'title'     =>  $pv->variant,
                ]);
            }
            array_push($variants,(object)[
                'title'     =>  $var->title,
                'subs'      =>  $sub
            ]);
        }
        
        return response()->json([
            'datatable'         =>  $datatable,
            'variants'          =>  $variants,
            'page'              =>  [
                'theads'        =>  [
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

    public function validation(Request $request)
    {
        $id = $request->id ?? null;
        return Validator::make($request->all(), [
            'title'                     => 'required|max:250|unique:products,title,'.$id,
            'sku'                       => 'required|max:250|unique:products,sku,'.$id,
            'description'               => 'required|max:6000',
            'product_variant.*'         => 'required',
            'product_variant_prices.*'  => 'required',
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $isValid = $this->validation($request);
        if ($isValid->fails()) {
            return response()->json(['errors' => $isValid->errors()->all()]);
        }
        $product = [
            'title'         =>  $request->title,
            'sku'           =>  $request->sku,
            'description'   =>  $request->description
        ];
        
        DB::beginTransaction();
        try {
            $productInfo = Product::create($product);
            $product_id = $productInfo->id;

            $images = [];
            if ($request->file('files')) {
                $num_elements = 0;
                $files = $request->file('files');
                while ($num_elements < count($files)) {
                    $upload = $files[$num_elements];
    
                    $mime = $upload->getClientOriginalExtension();
                    $name = md5(rand(1,time())).'.'.$mime;
                    $path = $upload->move('image',$name);
                    $images[] = [
                        'product_id'    =>  $product_id,
                        'file_path'     =>  $path,
                        'created_at'    =>  now(),
                        'updated_at'    =>  now(),
                    ];
                    $num_elements++;
                }
            }
            ProductImage::insert($images);
            
            $variant_prices = [];
            foreach (json_decode($request->product_variant_prices) as $variant_price) {
                $variants = explode('/',$variant_price->title);
                $variant_prices[] = [
                    'product_variant_one'       =>  !empty($variants[0]) ? trim($variants[0]) : null,
                    'product_variant_two'       =>  !empty($variants[1]) ? trim($variants[1]) : null,
                    'product_variant_three'     =>  !empty($variants[2]) ? trim($variants[2]) : null,
                    'price'                     =>  $variant_price->price,
                    'stock'                     =>  $variant_price->stock,
                    'product_id'                =>  $product_id,
                    'created_at'                =>  now(),
                    'updated_at'                =>  now(),
                ];
            }

            foreach (json_decode($request->product_variant) as $variant) {
                foreach ($variant->tags as $key => $tag) {
                    $pvInfo = ProductVariant::create([
                        'variant'       =>  $tag,
                        'variant_id'    =>  (int)$variant->option,
                        'product_id'    =>  $product_id
                    ]);

                    foreach($variant_prices as $k => $vp){
                        if ($vp['product_variant_one'] == $tag) {
                            $variant_prices[$k]['product_variant_one'] = $pvInfo->id;
                        }
                        if ($vp['product_variant_two'] == $tag) {
                            $variant_prices[$k]['product_variant_two'] = $pvInfo->id;
                        }
                        if ($vp['product_variant_three'] == $tag) {
                            $variant_prices[$k]['product_variant_three'] = $pvInfo->id;
                        }
                    }
                }
            }
            ProductVariantPrice::insert($variant_prices);
            DB::commit();
            return response()->json(['success'  =>  'Successfully Saved']);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['errors' => ['There is a problem please try again']]);
        }
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
        $isValid = $this->validation($request);
        if ($isValid->fails()) {
            return response()->json(['errors' => $isValid->errors()->all()]);
        }
        $product = [
            'title'         =>  $request->title,
            'sku'           =>  $request->sku,
            'description'   =>  $request->description
        ];
        
        DB::beginTransaction();
        try {
            $product_id = $request->id;
            ProductVariant::where('product_id',$product_id)->delete();
            ProductVariantPrice::where('product_id',$product_id)->delete();

            Product::where('id',$product_id)->update($product);

            $images = [];
            if ($request->file('files')) {
                $num_elements = 0;
                $files = $request->file('files');
                while ($num_elements < count($files)) {
                    $upload = $files[$num_elements];
    
                    $mime = $upload->getClientOriginalExtension();
                    $name = md5(rand(1,time())).'.'.$mime;
                    $path = $upload->move('image',$name);
                    $images[] = [
                        'product_id'    =>  $product_id,
                        'file_path'     =>  $path,
                        'created_at'    =>  now(),
                        'updated_at'    =>  now(),
                    ];
                    $num_elements++;
                }
            }
            ProductImage::insert($images);
            
            $variant_prices = [];
            foreach (json_decode($request->product_variant_prices) as $variant_price) {
                $variants = explode('/',$variant_price->title);
                $variant_prices[] = [
                    'product_variant_one'       =>  !empty($variants[0]) ? trim($variants[0]) : null,
                    'product_variant_two'       =>  !empty($variants[1]) ? trim($variants[1]) : null,
                    'product_variant_three'     =>  !empty($variants[2]) ? trim($variants[2]) : null,
                    'price'                     =>  $variant_price->price,
                    'stock'                     =>  $variant_price->stock,
                    'product_id'                =>  $product_id,
                    'created_at'                =>  now(),
                    'updated_at'                =>  now(),
                ];
            }

            foreach (json_decode($request->product_variant) as $variant) {
                foreach ($variant->tags as $key => $tag) {
                    $pvInfo = ProductVariant::create([
                        'variant'       =>  $tag,
                        'variant_id'    =>  (int)$variant->option,
                        'product_id'    =>  $product_id
                    ]);

                    foreach($variant_prices as $k => $vp){
                        if ($vp['product_variant_one'] == $tag) {
                            $variant_prices[$k]['product_variant_one'] = $pvInfo->id;
                        }
                        if ($vp['product_variant_two'] == $tag) {
                            $variant_prices[$k]['product_variant_two'] = $pvInfo->id;
                        }
                        if ($vp['product_variant_three'] == $tag) {
                            $variant_prices[$k]['product_variant_three'] = $pvInfo->id;
                        }
                    }
                }
            }
            ProductVariantPrice::insert($variant_prices);
            DB::commit();
            return response()->json(['success'  =>  'Successfully Saved']);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['errors' => ['There is a problem please try again',$th]]);
        }
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
