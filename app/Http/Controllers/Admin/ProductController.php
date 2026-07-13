<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Category;
use App\Models\ProductColor; // ⬅️ ADDED: Necessary for direct color model interaction
use App\Models\ProductVariant; // ⬅️ ADDED: Necessary for variant upsert on edit
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
            'q'          => $request->q,
            'offset'     => $request->offset,
            'categories' => \App\Models\Category::all()
        ]);
    }

    /**
     * Fetch Products rows for AJAX listing (with gender/flag filters and stats).
     */
    public function listProducts(Request $request)
    {
        $query = Products::with('category');
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhereHas('category', function ($q2) use ($request) {
                      $q2->where('name', 'like', "%{$request->q}%");
                  });
            });
        }

        if ($request->gender) {
            $query->where('gender', $request->gender);
        }

        if ($request->flag === 'best_seller') {
            $query->where('best_seller', 1);
        } elseif ($request->flag === 'featured') {
            $query->where('is_featured', 1);
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        // Stats (unfiltered totals)
        $stats = [
            'total'       => Products::count(),
            'best_seller' => Products::where('best_seller', 1)->count(),
            'featured'    => Products::where('is_featured', 1)->count(),
            'categories'  => \App\Models\Category::count(),
        ];

        $data = [
            'rows'       => view('admin.modules.products.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
            'stats'      => $stats,
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
        'is_qikink_product'       => 'nullable',
        'qikink_sku'              => 'nullable|string|max:255',
        'qikink_print_type_id'    => 'nullable|integer',
        'search_from_my_products' => 'nullable',
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
        'is_qikink_product'       => $request->has('is_qikink_product') ? 1 : 0,
        'qikink_sku'              => $request->qikink_sku,
        'qikink_print_type_id'    => $request->qikink_print_type_id ?? 1,
        'search_from_my_products' => $request->has('search_from_my_products') ? 1 : 0,
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

    // Handle colors & variants
    if ($request->has('colors')) {
        foreach ($request->colors as $colorInput) {
            if (empty($colorInput['color'])) continue;

            $colorId   = $colorInput['id'] ?? null;
            $newImages = [];

            // Collect newly uploaded images
            if (isset($colorInput['images']) && is_array($colorInput['images'])) {
                foreach ($colorInput['images'] as $img) {
                    if ($img instanceof \Illuminate\Http\UploadedFile && $img->isValid()) {
                        $newImages[] = $img->store('product-colors', 'public');
                    }
                }
            }

            if ($colorId) {
                // ── UPDATE existing color ──────────────────────────────────
                $productColor = \App\Models\ProductColor::find($colorId);
                if (!$productColor) continue;

                $updateData = ['color' => $colorInput['color']];

                if (!empty($newImages)) {
                    // Delete old images from storage (model casts to array already)
                    $oldImages = is_array($productColor->images) ? $productColor->images : [];
                    foreach ($oldImages as $img) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($img);
                    }
                    // Pass plain array — model will json_encode via cast
                    $updateData['images'] = $newImages;
                }

                $productColor->update($updateData);

                // ── UPDATE / CREATE / DELETE variants for existing color ───
                if (isset($colorInput['variants']) && is_array($colorInput['variants'])) {
                    $submittedVariantIds = [];

                    foreach ($colorInput['variants'] as $variantData) {
                        if (empty($variantData['size']) && empty($variantData['price'])) continue;

                        $price    = floatval($variantData['price']    ?? 0);
                        $discount = floatval($variantData['discount'] ?? 0);
                        $total    = $discount > 0 ? $price - ($price * $discount / 100) : $price;

                        $variantId = $variantData['id'] ?? null;

                        if ($variantId) {
                            // Update existing variant row
                            $existingVariant = \App\Models\ProductVariant::find($variantId);
                            if ($existingVariant && $existingVariant->color_id == $colorId) {
                                $existingVariant->update([
                                    'size'        => $variantData['size']  ?? '',
                                    'stock'       => intval($variantData['stock'] ?? 0),
                                    'price'       => $price,
                                    'discount'    => $discount,
                                    'total_price' => round($total, 2),
                                ]);
                                $submittedVariantIds[] = $existingVariant->id;
                            }
                        } else {
                            // Create new variant row
                            $newVariant = $productColor->variants()->create([
                                'product_id'  => $product->id,
                                'size'        => $variantData['size']  ?? '',
                                'stock'       => intval($variantData['stock'] ?? 0),
                                'price'       => $price,
                                'discount'    => $discount,
                                'total_price' => round($total, 2),
                            ]);
                            $submittedVariantIds[] = $newVariant->id;
                        }
                    }

                    // Delete variants that were removed from the form
                    $productColor->variants()
                        ->whereNotIn('id', $submittedVariantIds)
                        ->delete();
                }

            } else {
                // ── CREATE new color ───────────────────────────────────────
                // Pass plain array — model cast handles encoding
                $productColor = $product->colors()->create([
                    'color'  => $colorInput['color'],
                    'images' => $newImages,   // array, not json_encode()
                ]);

                // Create variants for this new color
                if (!empty($colorInput['variants']) && is_array($colorInput['variants'])) {
                    foreach ($colorInput['variants'] as $variantData) {
                        if (empty($variantData['size']) && empty($variantData['price'])) continue;
                        $price    = floatval($variantData['price']    ?? 0);
                        $discount = floatval($variantData['discount'] ?? 0);
                        $total    = $discount > 0 ? $price - ($price * $discount / 100) : $price;
                        $productColor->variants()->create([
                            'product_id'  => $product->id,
                            'size'        => $variantData['size']  ?? '',
                            'stock'       => intval($variantData['stock'] ?? 0),
                            'price'       => $price,
                            'discount'    => $discount,
                            'total_price' => round($total, 2),
                        ]);
                    }
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

    /**
     * Quick create/import product using Qikink SKU.
     */
    public function qikinkQuickCreate(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'qikink_sku'  => 'required|string|max:255',
            'base_price'  => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'gender'      => 'required|in:male,female,unisex',
        ]);

        try {
            // Check if product already exists with this slug
            $slug = \Illuminate\Support\Str::slug($request->name);
            $slugCount = Products::where('slug', $slug)->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . time();
            }

            // Create product
            $product = Products::create([
                'name'                    => $request->name,
                'slug'                    => $slug,
                'category_id'             => $request->category_id,
                'gender'                  => $request->gender,
                'description'             => 'Quick created Qikink product (SKU: ' . $request->qikink_sku . ')',
                'customizable'            => 1, // Default customizable
                'is_qikink_product'       => 1,
                'qikink_sku'              => $request->qikink_sku,
                'qikink_print_type_id'    => 1, // DTG by default
                'search_from_my_products' => 1, // Default Search from My Products = True for quick adds
                'best_seller'             => 0,
                'is_featured'             => 0,
            ]);

            // Add a default color block (e.g. "Default")
            $color = \App\Models\ProductColor::create([
                'product_id' => $product->id,
                'color'      => 'Default',
                'images'     => [] // empty array
            ]);

            // Add standard size variants: S, M, L, XL, XXL
            $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
            $price = floatval($request->base_price);
            
            foreach ($sizes as $size) {
                \App\Models\ProductVariant::create([
                    'color_id'    => $color->id,
                    'size'        => $size,
                    'stock'       => 100, // standard high stock
                    'price'       => $price,
                    'discount'    => 0,
                    'total_price' => $price
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product imported and created successfully! (SKU: ' . $request->qikink_sku . ')',
                'redirect'=> route('products.index')
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Qikink Quick Import failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }
}