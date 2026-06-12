<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductColor;
use App\Models\Products;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // ⬅️ ADDED: For scoped unique validation

class ProductColorController extends Controller
{
    // List page
    public function index()
    {
        return view('admin.modules.product-colors.list');
    }

    // AJAX: fetch rows
    public function listColors(Request $request)
    {
        $query = ProductColor::with('product');

        $items = $query->orderBy('id','desc')->paginate($request->offset ?? 10);

        $data = [
            'rows'       => view('admin.modules.product-colors.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    // Show create form
    public function create()
    {
        $products = Products::all();
        return view('admin.modules.product-colors.add', [
            'item' => false,
            'products' => $products
        ]);
    }

    // Show edit form
    public function edit($id)
    {
        $item = ProductColor::findOrFail($id);
        $products = Products::all();
        return view('admin.modules.product-colors.add', [
            'item' => $item,
            'products' => $products
        ]);
    }

    // Store / Update
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            
            // ⬅️ CRITICAL FIX: Scoped uniqueness validation
            'color'      => [
                'required',
                'string',
                'max:50',
                Rule::unique('product_colors')->where(function ($query) use ($request) {
                    return $query->where('product_id', $request->product_id);
                })->ignore($request->id), // Ignore the current color record when updating
            ],
            
            'images.*'   => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp,avif' 
        ]);

        $data = [
            'product_id' => $request->product_id,
            'color'      => $request->color
        ];

        // 1. Handle new images and store them in $newImages array
        $newImages = [];
        if($request->hasFile('images')){
            foreach($request->file('images') as $img){
                $newImages[] = $img->store('product-colors','public');
            }
        }
        
        // 2. Logic for Update/Create
        if($request->id){
            $color = ProductColor::findOrFail($request->id);

            // Start with existing images (automatically array due to model casting)
            $currentImages = $color->images ?? []; 

            // Delete removed images
            if($request->removed_images){
                foreach(json_decode($request->removed_images,true) as $imgToDelete){
                    Storage::disk('public')->delete($imgToDelete);
                    // Remove from the current images array
                    if(($key = array_search($imgToDelete, $currentImages)) !== false){
                        unset($currentImages[$key]);
                    }
                }
            }
            
            // Merge remaining old images (array_values resets keys) with the newly uploaded images
            $finalImages = array_merge(array_values($currentImages), $newImages);
            $data['images'] = $finalImages; // Model casting will handle this array automatically

            $color->update($data);
            $message = "Color Updated";
        } else {
            // For creation, just use the new images (if any)
            if (!empty($newImages)) {
                 $data['images'] = $newImages; 
            }
            ProductColor::create($data); // Model casting handles this array automatically
            $message = "Color Added";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect'=> route('product-colors.index')
        ]);
    }

    // Delete color
    public function delete(Request $request)
    {
        $color = ProductColor::findOrFail($request->id);

        // Model casting ensures $color->images is already a PHP array
        if($color->images && is_array($color->images)){
            foreach($color->images as $img){
                Storage::disk('public')->delete($img);
            }
        }

        $color->delete();

        return response()->json(['message'=>'Color Deleted'],200);
    }
}
