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
            'slug'        => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'gender'      => 'required|in:male,female,unisex',
            'main_image'  => $request->id ? ['nullable', new \App\Rules\ValidImageFile] : ['required', new \App\Rules\ValidImageFile],
            'zoomed_image'=> ['nullable', new \App\Rules\ValidImageFile],
            'colors.*.color' => 'nullable|string|max:50',
            'is_qikink_product'       => 'nullable',
            'qikink_sku'              => 'nullable|string|max:255',
            'qikink_print_type_id'    => 'nullable|integer',
            'search_from_my_products' => 'nullable',
        ]);

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                // Generate a unique slug automatically to avoid duplicate slug failures
                $userSlug = $request->slug ? \Illuminate\Support\Str::slug($request->slug) : \Illuminate\Support\Str::slug($request->name);
                $baseSlug = $userSlug ?: 'product-' . time();
                $slug = $baseSlug;
                $count = 1;

                while (Products::where('slug', $slug)->where('id', '!=', $request->id)->exists()) {
                    $slug = "{$baseSlug}-{$count}";
                    $count++;
                }

                $data = [
                    'name'        => $request->name,
                    'slug'        => $slug,
                    'category_id' => $request->category_id,
                    'gender'      => $request->gender,
                    'description' => $request->description,
                    'best_seller' => $request->has('best_seller') ? 1 : 0,
                    'is_featured' => $request->has('is_featured') ? 1 : 0,
                    'is_qikink_product'       => $request->has('is_qikink_product') ? 1 : 0,
                    'qikink_sku'              => $request->qikink_sku,
                    'qikink_print_type_id'    => $request->qikink_print_type_id ?? 1,
                    'search_from_my_products' => $request->has('search_from_my_products') ? 1 : 0,
                    // Combo product fields
                    'is_combo'    => $request->has('is_combo') ? 1 : 0,
                    'combo_type'  => $request->input('combo_type'),
                    'male_sizes'  => $request->has('is_combo') && $request->input('male_sizes')
                        ? array_values(array_filter(array_map('trim', explode(',', $request->input('male_sizes')))))
                        : null,
                    'female_sizes'=> $request->has('is_combo') && $request->input('female_sizes')
                        ? array_values(array_filter(array_map('trim', explode(',', $request->input('female_sizes')))))
                        : null,
                ];

                // Handle main image
                if ($request->hasFile('main_image')) {
                    if ($request->id && $product = Products::find($request->id)) {
                        if ($product->main_image) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->main_image);
                        }
                    }
                    $data['main_image'] = \App\Helpers\ImageHelper::convertAndStoreToWebp($request->file('main_image'), 'products');
                }

                // Handle zoomed image with fallback to main_image
                if ($request->hasFile('zoomed_image')) {
                    if ($request->id && $product = Products::find($request->id)) {
                        if ($product->zoomed_image) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->zoomed_image);
                        }
                    }
                    $data['zoomed_image'] = \App\Helpers\ImageHelper::convertAndStoreToWebp($request->file('zoomed_image'), 'products');
                } elseif (!empty($data['main_image']) && empty($data['zoomed_image']) && !$request->id) {
                    $data['zoomed_image'] = $data['main_image'];
                }

                // Create or update product
                if ($request->id) {
                    $product = Products::findOrFail($request->id);
                    $product->update($data);
                    $message = 'Product Updated Successfully';
                } else {
                    $product = Products::create($data);
                    $message = 'Product Added Successfully';
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
                                    $newImages[] = \App\Helpers\ImageHelper::convertAndStoreToWebp($img, 'product-colors');
                                }
                            }
                        }

                        if ($colorId) {
                            // UPDATE existing color
                            $productColor = \App\Models\ProductColor::find($colorId);
                            if (!$productColor) continue;

                            $updateData = ['color' => $colorInput['color']];

                            if (!empty($newImages)) {
                                $oldImages = is_array($productColor->images) ? $productColor->images : [];
                                foreach ($oldImages as $img) {
                                    \Illuminate\Support\Facades\Storage::disk('public')->delete($img);
                                }
                                $updateData['images'] = $newImages;
                            }

                            $productColor->update($updateData);

                            // UPDATE / CREATE / DELETE variants for existing color
                            if (isset($colorInput['variants']) && is_array($colorInput['variants'])) {
                                $submittedVariantIds = [];

                                foreach ($colorInput['variants'] as $variantData) {
                                    if (empty($variantData['size']) && empty($variantData['price'])) continue;

                                    $price    = floatval($variantData['price']    ?? 0);
                                    $discount = floatval($variantData['discount'] ?? 0);
                                    $total    = $discount > 0 ? $price - ($price * $discount / 100) : $price;
                                    $variantId = $variantData['id'] ?? null;

                                    if ($variantId) {
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

                                $productColor->variants()
                                    ->whereNotIn('id', $submittedVariantIds)
                                    ->delete();
                            }

                        } else {
                            // CREATE new color
                            $productColor = $product->colors()->create([
                                'color'  => $colorInput['color'],
                                'images' => $newImages,
                            ]);

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
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to save product: ' . $e->getMessage()
            ], 422);
        }
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

        // Delete main and zoomed images if they exist
        $imagesToDelete = array_filter([$product->main_image, $product->zoomed_image]);
        if (!empty($imagesToDelete)) {
            Storage::disk('public')->delete($imagesToDelete);
        }

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
                    'product_id'  => $product->id,
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