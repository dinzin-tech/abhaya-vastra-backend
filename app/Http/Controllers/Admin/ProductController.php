<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Category;
use App\Models\ProductColor; // ⬅️ ADDED: Necessary for direct color model interaction
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile; // ⬅️ ADDED: For robust file type checking
use Illuminate\Validation\Rule;


class ProductController extends Controller
{
    /**
     * Display the Products listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.products.list', [
            'q'      => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch Products rows for AJAX listing.
     */
    public function listProducts(Request $request)
    {
        $query = Products::with('category');
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('name', 'like', "%{$request->q}%")
                  ->orWhereHas('category', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->q}%");
                  });
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.products.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new Product.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.modules.products.add', [
            'item'       => false,
            'categories' => $categories
        ]);
    }

    /**
     * Store or update a Product along with Colors.
     */
 public function store(Request $request)
{
    // 1. Validation for main product fields
    $request->validate([
        'name'        => 'required|string|max:255',
        'slug'        => [
                                'required',
                                'string',
                                'max:255',
                                Rule::unique('products')->ignore($request->id),
                            ],

        'category_id' => 'required|exists:categories,id',
        'gender'      => 'required|in:male,female,unisex', // ✅ Validate gender
        // 'price'       => 'required|numeric',
        // 'discount'    => 'nullable|numeric',
        'main_image'  => $request->id ? 'nullable|mimes:jpeg,png,bmp,gif,svg,webp,avif' : 'required|mimes:jpeg,png,bmp,gif,svg,webp,avif',
        'zoomed_image'=> $request->id ? 'nullable|mimes:jpeg,png,bmp,gif,svg,webp,avif' : 'required|mimes:jpeg,png,bmp,gif,svg,webp,avif',
        'colors.*.color' => 'nullable|string|max:50',
        
    ]);

    // $price = $request->price;
    // $discount = $request->discount ?? 0;
    // $totalPrice = $discount > 0 ? $price - ($price * ($discount / 100)) : $price;

    $data = [
        'name'        => $request->name,
        'slug'        => \Illuminate\Support\Str::slug($request->name),
        'category_id' => $request->category_id,
        'gender'      => $request->gender, // ✅ Take from form
        'description' => $request->description,
        // 'price'       => $price,
        // 'discount'    => $discount,
        // 'total_price' => $totalPrice,
        'best_seller' => $request->has('best_seller') ? 1 : 0,
        'is_featured' => $request->has('is_featured') ? 1 : 0,

    ];

    // Handle main image
    if ($request->hasFile('main_image')) {
        if ($request->id && $product = \App\Models\Products::find($request->id)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->main_image);
        }
        $data['main_image'] = $request->file('main_image')->store('products', 'public');
    }

    // Handle zoomed image
    if ($request->hasFile('zoomed_image')) {
        if ($request->id && $product = \App\Models\Products::find($request->id)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->zoomed_image);
        }
        $data['zoomed_image'] = $request->file('zoomed_image')->store('products', 'public');
    }

    // Create or update product
    if ($request->id) {
        $product = \App\Models\Products::findOrFail($request->id);
        $product->update($data);
        $message = 'Product Updated';
    } else {
        $product = \App\Models\Products::create($data);
        $message = 'Product Added';
    }

    // Handle colors
    if ($request->has('colors')) {
        foreach ($request->colors as $colorInput) {
            if (empty($colorInput['color'])) continue;

            $colorId = $colorInput['id'] ?? null;
            $newImages = [];

            if (isset($colorInput['images']) && is_array($colorInput['images'])) {
                foreach ($colorInput['images'] as $img) {
                    if ($img instanceof \Illuminate\Http\UploadedFile && $img->isValid()) {
                        $newImages[] = $img->store('product-colors','public');
                    }
                }
            }

            if ($colorId) {
                $productColor = \App\Models\ProductColor::find($colorId);
                if ($productColor) {
                    $updateData = ['color' => $colorInput['color']];
                    if (!empty($newImages)) {
                        $oldImages = json_decode($productColor->images, true) ?? [];
                        foreach ($oldImages as $img) \Illuminate\Support\Facades\Storage::disk('public')->delete($img);
                        $updateData['images'] = json_encode($newImages);
                    }
                    $productColor->update($updateData);
                }
            } else {
                if (!empty($newImages)) {
                    $product->colors()->create([
                        'color'  => $colorInput['color'],
                        'images' => json_encode($newImages)
                    ]);
                }
            }
        }
    }

    return response()->json([
        'success'  => true,
        'message'  => $message,
        'redirect' => route('products.index')
    ]);
}

    /**
     * Show the form for editing a Product.
     */
    public function edit($id)
    {
        $item = Products::with('colors.variants')->findOrFail($id);
        $categories = Category::all();
        return view('admin.modules.products.add', [
            'item'       => $item,
            'categories' => $categories
        ]);
    }

    /**
     * Delete a Product along with colors and variants.
     */
    public function delete(Request $request)
    {
        $product = Products::with('colors.variants')->findOrFail($request->id);

        // Delete product colors and variants
        // foreach($product->colors as $color){
        //     $images = json_decode($color->images, true) ?? [];
        //     foreach($images as $img) Storage::disk('public')->delete($img);
        //     $color->variants()->delete();
        //     $color->delete();
        // }

        // Delete main and zoomed images
        Storage::disk('public')->delete([$product->main_image, $product->zoomed_image]);

        $product->delete();

        return response()->json([
            'message' => 'Product Deleted Successfully!',
        ], 200);
    }
}