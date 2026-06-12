<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Products;
use App\Models\ProductColor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // ⬅️ ADDED: For scoped unique validation

class ProductVariantController extends Controller
{
    /**
     * List all variants page
     */
    public function index()
    {
        return view('admin.modules.product-variants.list');
    }

    /**
     * AJAX: Fetch variant rows
     */
    public function listVariants(Request $request)
    {
        $query = ProductVariant::with('product', 'color');

        if ($request->q) {
            $query->whereHas('product', function($q) use ($request){
                $q->where('name','like',"%{$request->q}%");
            });
        }

        $items = $query->orderBy('id','desc')->paginate($request->offset ?? 10);

        $data = [
            'rows'       => view('admin.modules.product-variants.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data,200);
    }

    /**
     * Show form to add variant
     */
    public function create()
    {
        $products = Products::all(); 
        $productColors = ProductColor::all();

        return view('admin.modules.product-variants.add', [
            'item'          => false,
            'products'      => $products,
            'productColors' => $productColors 
        ]);
    }

    /**
     * Show form to edit variant
     */
    public function edit($id)
    {
        $item = ProductVariant::findOrFail($id);
        $products = Products::all();
        $productColors = ProductColor::all();

        return view('admin.modules.product-variants.add', [
            'item'          => $item,
            'products'      => $products,
            'productColors' => $productColors
        ]);
    }

    /**
     * Store or update variant (Scoped unique check)
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'product_id' => 'required|exists:products,id',
    //         'color_id'   => 'required|exists:product_colors,id',
    //         'size'       => [
    //             'required',
    //             'string',
    //             'max:50',
    //             // Ensure size is unique for a specific product_id AND color_id
    //             Rule::unique('product_variants')->where(function ($query) use ($request) {
    //                 return $query
    //                     ->where('product_id', $request->product_id)
    //                     ->where('color_id', $request->color_id);
    //             })->ignore($request->id), // Ignore current ID when updating
    //         ],
    //         'stock'      => 'nullable|integer',
    //         'price'      => 'required|numeric|min:0', 
    //     ]);

    //     $data = $request->only(['product_id','color_id','size','stock', 'price']); 

    //     if($request->id){
    //         $variant = ProductVariant::findOrFail($request->id);
    //         $variant->update($data);
    //         $message = 'Variant Updated';
    //     } else {
    //         ProductVariant::create($data);
    //         $message = 'Variant Added';
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => $message,
    //         'redirect'=> route('product-variants.index')
    //     ]);
    // }

    public function store(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'color_id'   => 'required|exists:product_colors,id',
        'size'       => [
            'required',
            'string',
            'max:50',
            Rule::unique('product_variants')->where(function ($query) use ($request) {
                return $query
                    ->where('product_id', $request->product_id)
                    ->where('color_id', $request->color_id);
            })->ignore($request->id),
        ],
        'stock'      => 'nullable|integer',
        'weight'     => 'nullable|numeric',
        'price'      => 'required|numeric|min:0',
        'discount'   => 'nullable|numeric|min:0|max:100',
    ]);

    // Calculate total price
    $price = $request->price;
    $discount = $request->discount ?? 0;
    $totalPrice = $discount > 0 ? $price - ($price * ($discount / 100)) : $price;

    // Prepare data
    $data = [
        'product_id'   => $request->product_id,
        'color_id'     => $request->color_id,
        'size'         => $request->size,
        'stock'        => $request->stock,
        'weight'       => $request->weight,
        'price'        => $price,
        'discount'     => $discount,
        'total_price'  => $totalPrice,
    ];

    if ($request->id) {
        $variant = ProductVariant::findOrFail($request->id);
        $variant->update($data);
        $message = 'Variant Updated';
    } else {
        ProductVariant::create($data);
        $message = 'Variant Added';
    }

    return response()->json([
        'success' => true,
        'message' => $message,
        'redirect'=> route('product-variants.index')
    ]);
}


    /**
     * Delete variant
     */
    public function delete(Request $request)
    {
        $variant = ProductVariant::findOrFail($request->id);
        $variant->delete();

        return response()->json(['message'=>'Variant Deleted'],200);
    }
    
    public function getProductColorsByProduct(Request $request)
    {
        // Validate input to ensure product_id is sent
        $request->validate(['product_id' => 'required|exists:products,id']);
        
        // Query colors associated with the requested product ID
        $colors = ProductColor::where('product_id', $request->product_id)->get(['id', 'color']);
        
        return response()->json($colors);
    }
}