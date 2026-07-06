<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display all products including customizable items with structured API response.
     */
    public function index(Request $request)
    {
        // 1️⃣ Regular products
        $query = Products::with([
            'category:id,name,gender',
            'colors:id,product_id,color,images',
            'variants:id,product_id,color_id,size,stock,price,discount,total_price'
        ])
        ->select('id', 'category_id', 'name', 'main_image', 'zoomed_image', 'description' , 'gender' , 'slug')
        ->orderBy('created_at', 'desc');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->get()->map(function ($product) {
            $colorImages = $product->colors->pluck('images')->flatten()->filter()->unique()->values()->toArray();

            $multipleImages = [];
            if (!empty($colorImages)) {
                $multipleImages = array_map(fn($img) => asset('storage/products/' . basename($img)), $colorImages);
            } else {
                $multipleImages = [
                    $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                    $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null
                ];
            }

            // Calculate total stock across all variants
            $totalStock = $product->variants->sum('stock');
            
            // Get variants with full data from variant's own price fields
            $variantsData = $product->variants->map(function($v) {
                return [
                    'id' => $v->id,
                    'size' => $v->size,
                    'stock' => $v->stock ?? 0,
                    'color_id' => $v->color_id,
                    'price' => $v->price ?? 0,
                    'discount' => $v->discount ?? 0,
                    'total_price' => $v->total_price ?? $v->price ?? 0
                ];
            })->toArray();
            
            // Get min price from variants for display
            $minPrice = $product->variants->min('total_price') ?? 0;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name ?? null,
                'price' => $minPrice,
                'total_price' => $minPrice,
                'gender' => $product->gender ?? 'unisex',
                'color' => $product->colors->first()->color ?? 'black',
                'sizes' => $product->variants->pluck('size')->unique()->values()->toArray() ?: ['S', 'M', 'L', 'XL'],
                'variants' => $variantsData,
                'image' => $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                'hoverImage' => $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null,
                'description' => $product->description,
                'multipleImages' => $multipleImages,
                'colors' => $product->colors->pluck('color')->unique()->values()->toArray() ?: ['black', 'blue', 'red', 'green'],
                'customizable' => false,
                'slug' => $product->slug,
                'total_stock' => $totalStock
            ];
        });

   $customProducts = DB::table('customizeds')->get()->map(function ($item) {
    // Decode JSON safely
    $imagesArray = json_decode($item->images, true);

    // If still string, decode again
    if (is_string($imagesArray)) {
        $imagesArray = json_decode($imagesArray, true);
    }

    // Ensure it's an array
    $imagesArray = is_array($imagesArray) ? $imagesArray : [];

    // Map to full URLs
    $fullImages = array_map(fn($img) => asset('storage/' . $img), $imagesArray);

    return [
        'id' => $item->id,
        'name' => $item->title,
        'category' => 'Custom',
        'price' => null,
        'gender' => 'unisex',
        'color' => null,
        'sizes' => [],
        'image' => $fullImages[0] ?? null,
        'hoverImage' => $fullImages[1] ?? null,
        'description' => $item->description,
        'multipleImages' => $fullImages,
        'colors' => [],
        'customizable' => true,
        'slug' => $item->slug
    ];
});




        // Merge both arrays
        $allProducts = $products->merge($customProducts)->values();

        return response()->json([
            'success' => true,
            'message' => 'All products fetched successfully',
            'data' => $allProducts
        ]);
    }

    /**
     * Show individual product details (regular products only).
     */
    public function show($id)
    {   
         
        try {
            $customizable = request()->boolean('customizable', false);

            if ($customizable) {
                // Fetch customizable product from customizeds table
                $customData = \DB::table('customizeds')
                    ->where('slug','=' , $id)
                    ->select('id', 'title', 'description', 'images')
                    ->get()
                    ->map(function ($item) {
                        $imagesArray = json_decode($item->images, true);

                        // Decode again if it's still a string
                        if (is_string($imagesArray)) {
                            $imagesArray = json_decode($imagesArray, true);
                        }

                        $imagesArray = is_array($imagesArray) ? $imagesArray : [];

                        $fullImages = array_map(fn($img) => asset('storage/customized/' . basename($img)), $imagesArray);

                        return [
                            'id' => $item->id,
                            'name' => $item->title,
                            'title' => $item->title,
                            'description' => $item->description,
                            'image' => $fullImages[0] ?? null,
                            'images' => $fullImages,
                        ];
                    });

                $allCustomImages = $customData->flatMap(fn($item) => $item['images'])->unique()->values()->toArray();
                $firstCustom = $customData->first();

                $response = [
                    'id' => $id,
                'name' => $firstCustom['name'] ,
                    'title' => $firstCustom['name'],

                    'customizable' => true,
                    'category' => null, // Optional: could fetch category if needed
                    'price' => null,    // Optional: could fetch price if needed
                    'gender' => 'unisex',
                    'description' => $customData->first()->description ?? null,
                    'variants' => [],
                    'sizes' => [],
                    'multipleImages' => $allCustomImages,
                    'custom_options' => $customData,
                    'colors' => [],
                ];
            } else {
                // Fetch normal product from products table
                // Try to find by ID first if numeric, otherwise by slug
                $query = Products::with([
                    'category:id,name,gender',
                    'colors:id,product_id,color,images',
                    'variants:id,product_id,color_id,size,stock,price,discount,total_price',
                    'reviews:id,product_id,user_id,name,review,rating,image,created_at'
                ])
                ->select('id', 'category_id', 'gender',  'name', 'main_image', 'zoomed_image', 'description');
                
                if (is_numeric($id)) {
                    $product = $query->where('id', '=', $id)->first();
                } else {
                    $product = $query->where('slug', '=', $id)->first();
                }
                if (!$product) {
                    $customData = \DB::table('customizeds')
                    ->where('slug','=' , $id)
                    ->select('id', 'title', 'description', 'images')
                    ->get()
                    ->map(function ($item) {
                        $imagesArray = json_decode($item->images, true);

                        // Decode again if it's still a string
                        if (is_string($imagesArray)) {
                            $imagesArray = json_decode($imagesArray, true);
                        }

                        $imagesArray = is_array($imagesArray) ? $imagesArray : [];

                        $fullImages = array_map(fn($img) => asset('storage/customized/' . basename($img)), $imagesArray);

                        return [
                            'id' => $item->id,
                            'name' => $item->title,
                            'title' => $item->title,
                            'description' => $item->description,
                            'image' => $fullImages[0] ?? null,
                            'images' => $fullImages,
                        ];
                    });

                $allCustomImages = $customData->flatMap(fn($item) => $item['images'])->unique()->values()->toArray();
                $firstCustom = $customData->first();

                $response = [
                    'id' => $id,
                    'name' => $firstCustom['name'] ?? null,
                    'title' => $firstCustom['name'] ?? null,

                    'customizable' => true,
                    'category' => null, // Optional: could fetch category if needed
                    'price' => null,    // Optional: could fetch price if needed
                    'gender' => 'unisex',
                    'description' => $customData->first()->description ?? null,
                    'variants' => [],
                    'sizes' => [],
                    'multipleImages' => $allCustomImages,
                    'custom_options' => $customData,
                    'colors' => [],
                ];
                    return response()->json([
                        'data' => $response,
                        'success' => true,
                        'message' => 'Product Found',
                    ]);
                }

                // Get min price from variants for display
                $minPrice = $product->variants->min('total_price') ?? 0;
                $minDiscount = $product->variants->min('discount') ?? 0;
                
                $response = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'title' => $product->name,
                    'customizable' => false,
                    'category' => $product->category->name ?? null,
                    'price' => $minPrice,
                    'discount' => $minDiscount,
                    'total_price' => $minPrice,
                    'gender' => $product->gender ?? 'unisex',
                    'description' => $product->description,
                ];

                $variantData = $product->variants->map(function($v) {
                    return [
                        'id' => $v->id,
                        'size' => $v->size,
                        'stock' => $v->stock ?? 0,
                        'color_id' => $v->color_id,
                        'price' => $v->price ?? 0,
                        'discount' => $v->discount ?? 0,
                        'total_price' => $v->total_price ?? $v->price ?? 0
                    ];
                })->values()->toArray();

                $response['variants'] = $variantData;
                $response['sizes'] = collect($variantData)->pluck('size')->unique()->values()->toArray();

                $colorImages = $product->colors->map(function ($colorItem) {
                    $imagesArray = $colorItem->images;
                    if (is_string($imagesArray)) {
                        $imagesArray = explode(',', $imagesArray);
                    } elseif (!is_array($imagesArray)) {
                        $imagesArray = [];
                    }

                    $images = array_map(fn($img) => asset('storage/product-colors/' . basename($img)), $imagesArray);

                    return [
                        'id' => $colorItem->id,
                        'color' => $colorItem->color,
                        'images' => $images,
                    ];
                });

                $allImages = [];
                foreach ($colorImages as $c) {
                    $allImages = array_merge($allImages, $c['images']);
                }
                if ($product->main_image) $allImages[] = asset('storage/products/' . basename($product->main_image));
                if ($product->zoomed_image) $allImages[] = asset('storage/products/' . basename($product->zoomed_image));

                $response['multipleImages'] = array_values(array_unique($allImages));
                $response['colors'] = $colorImages;
                $response['reviews'] = $product->reviews ?? [];
            }

            return response()->json([
                'success' => true,
                'message' => 'Product details fetched successfully',
                'data' => $response
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }


    /**
     * Fetch best-selling products.
     */
    public function bestSellers(Request $request)
    {
        $products = Products::with([
            'category:id,name,gender',
            'colors:id,product_id,color,images',
            'variants:id,product_id,color_id,size,stock,price,discount,total_price'
        ])
        ->select('id', 'category_id', 'name', 'main_image', 'zoomed_image', 'description'  , 'gender' , 'slug')
        ->where('best_seller', '=', 1)
        ->orderBy('created_at', 'desc')
        ->get();

        $formatted = $products->map(function ($product) {
            $colorImages = $product->colors->pluck('images')->flatten()->filter()->unique()->values()->toArray();

            $multipleImages = [];
            if (!empty($colorImages)) {
                $multipleImages = array_map(fn($img) => asset('storage/products/' . basename($img)), $colorImages);
            } else {
                $multipleImages = [
                    $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                    $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null
                ];
            }
            
            // Calculate total stock across all variants
            $totalStock = $product->variants->sum('stock');
            
            // Get variants with full data from variant's own price fields
            $variantsData = $product->variants->map(function($v) {
                return [
                    'id' => $v->id,
                    'size' => $v->size,
                    'stock' => $v->stock ?? 0,
                    'color_id' => $v->color_id,
                    'price' => $v->price ?? 0,
                    'discount' => $v->discount ?? 0,
                    'total_price' => $v->total_price ?? $v->price ?? 0
                ];
            })->toArray();
            
            // Get min price from variants for display
            $minPrice = $product->variants->min('total_price') ?? 0;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name ?? null,
                'price' => $minPrice,
                'total_price' => $minPrice,
                'gender' => $product->gender ?? 'unisex',
                'color' => $product->colors->first()->color ?? 'black',
                'sizes' => $product->variants->pluck('size')->unique()->values()->toArray() ?: ['S', 'M', 'L', 'XL'],
                'variants' => $variantsData,
                'image' => $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                'hoverImage' => $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null,
                'description' => $product->description,
                'multipleImages' => $multipleImages,
                'colors' => $product->colors->pluck('color')->toArray() ?: ['black', 'blue', 'red', 'green'],
                'slug' => $product->slug,
                'total_stock' => $totalStock
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Best sellers fetched successfully',
            'data' => $formatted
        ]);
    }

    /**
     * Fetch featured products.
     */
    public function featuredProducts(Request $request)
    {
        $products = Products::with([
            'category:id,name,gender',
            'colors:id,product_id,color,images',
            'variants:id,product_id,color_id,size,stock,price,discount,total_price'
        ])
        ->select('id', 'category_id', 'name', 'main_image', 'zoomed_image', 'description', 'gender', 'slug')
        ->where('is_featured', '=', 1)
        ->orderBy('created_at', 'desc')
        ->get();

        $formatted = $products->map(function ($product) {
            $colorImages = $product->colors->pluck('images')->flatten()->filter()->unique()->values()->toArray();

            $multipleImages = [];
            if (!empty($colorImages)) {
                $multipleImages = array_map(fn($img) => asset('storage/products/' . basename($img)), $colorImages);
            } else {
                $multipleImages = [
                    $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                    $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null
                ];
            }
            
            $totalStock = $product->variants->sum('stock');
            
            // Get variants with full data from variant's own price fields
            $variantsData = $product->variants->map(function($v) {
                return [
                    'id' => $v->id,
                    'size' => $v->size,
                    'stock' => $v->stock ?? 0,
                    'color_id' => $v->color_id,
                    'price' => $v->price ?? 0,
                    'discount' => $v->discount ?? 0,
                    'total_price' => $v->total_price ?? $v->price ?? 0
                ];
            })->toArray();
            
            // Get min price from variants for display
            $minPrice = $product->variants->min('total_price') ?? 0;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name ?? null,
                'price' => $minPrice,
                'total_price' => $minPrice,
                'gender' => $product->gender ?? 'unisex',
                'color' => $product->colors->first()->color ?? 'black',
                'sizes' => $product->variants->pluck('size')->unique()->values()->toArray() ?: ['S', 'M', 'L', 'XL'],
                'variants' => $variantsData,
                'image' => $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                'hoverImage' => $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null,
                'description' => $product->description,
                'multipleImages' => $multipleImages,
                'colors' => $product->colors->pluck('color')->toArray() ?: ['black', 'blue', 'red', 'green'],
                'slug' => $product->slug,
                'total_stock' => $totalStock
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Featured products fetched successfully',
            'data' => $formatted
        ]);
    }

    /**
     * Fetch new arrivals (latest products).
     */
    public function newArrivals(Request $request)
    {
        $products = Products::with([
            'category:id,name,gender',
            'colors:id,product_id,color,images',
            'variants:id,product_id,color_id,size,stock,price,discount,total_price'
        ])
        ->select('id', 'category_id', 'name', 'main_image', 'zoomed_image', 'description', 'gender', 'slug')
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();
    
        $formatted = $products->map(function ($product) {
            $colorImages = $product->colors->pluck('images')->flatten()->filter()->unique()->values()->toArray();
    
            $multipleImages = [];
            if (!empty($colorImages)) {
                $multipleImages = array_map(fn($img) => asset('storage/products/' . basename($img)), $colorImages);
            } else {
                $multipleImages = [
                    $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                    $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null
                ];
            }
    
            $totalStock = $product->variants->sum('stock');
    
            // Get variants with full data from variant's own price fields
            $variantsData = $product->variants->map(function($v) {
                return [
                    'id' => $v->id,
                    'size' => $v->size,
                    'stock' => $v->stock ?? 0,
                    'color_id' => $v->color_id,
                    'price' => $v->price ?? 0,
                    'discount' => $v->discount ?? 0,
                    'total_price' => $v->total_price ?? $v->price ?? 0
                ];
            })->toArray();
            
            // Get min price from variants for display
            $minPrice = $product->variants->min('total_price') ?? 0;
    
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category->name ?? null,
                'price' => $minPrice,
                'total_price' => $minPrice,
                'gender' => $product->gender ?? 'unisex',
                'color' => $product->colors->first()->color ?? 'black',
                'sizes' => $product->variants->pluck('size')->unique()->values()->toArray() ?: ['S', 'M', 'L', 'XL'],
                'variants' => $variantsData,
                'image' => $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                'hoverImage' => $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null,
                'description' => $product->description,
                'multipleImages' => $multipleImages,
                'colors' => $product->colors->pluck('color')->toArray() ?: ['black', 'blue', 'red', 'green'],
                'slug' => $product->slug,
                'total_stock' => $totalStock
            ];
        });
    
        return response()->json([
            'success' => true,
            'message' => 'New arrivals fetched successfully',
            'data' => $formatted
        ]);
    }
    

  public function productsByCategory(Request $request)
{
    $categoryName = $request->query('category_name');

    if (!$categoryName) {
        return response()->json([
            'success' => false,
            'message' => 'Category name is required'
        ], 400);
    }

    // Normalize input: lowercase and replace hyphens with spaces
    $normalizedName = strtolower(str_replace('-', ' ', $categoryName));

    $products = Products::with([
        'category:id,name,gender',
        'colors:id,product_id,color,images',
        'variants:id,product_id,color_id,size,stock,price,discount,total_price'
    ])
    ->select('id', 'category_id', 'name', 'main_image', 'zoomed_image', 'description' , 'gender' ,'slug')
    ->whereHas('category', function($q) use ($normalizedName) {
        // Compare lowercase and replace hyphens in DB name too
        $q->whereRaw("LOWER(REPLACE(name, '-', ' ')) = ?", [$normalizedName]);
    })
    ->orderBy('created_at', 'desc')
    ->get()
    ->map(function ($product) {
        $colorImages = $product->colors->pluck('images')->flatten()->filter()->unique()->values()->toArray();

        $multipleImages = [];
        if (!empty($colorImages)) {
            $multipleImages = array_map(fn($img) => asset('storage/products/' . basename($img)), $colorImages);
        } else {
            $multipleImages = [
                $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null
            ];
        }

        // Calculate total stock across all variants
        $totalStock = $product->variants->sum('stock');
        
        // Get variants with full data from variant's own price fields
        $variantsData = $product->variants->map(function($v) {
            return [
                'id' => $v->id,
                'size' => $v->size,
                'stock' => $v->stock ?? 0,
                'color_id' => $v->color_id,
                'price' => $v->price ?? 0,
                'discount' => $v->discount ?? 0,
                'total_price' => $v->total_price ?? $v->price ?? 0
            ];
        })->toArray();
        
        // Get min price from variants for display
        $minPrice = $product->variants->min('total_price') ?? 0;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category->name ?? null,
            'price' => $minPrice,
            'total_price' => $minPrice,
            'gender' => $product->gender ?? 'unisex',
            'color' => $product->colors->first()->color ?? 'black',
            'sizes' => $product->variants->pluck('size')->unique()->values()->toArray() ?: ['S', 'M', 'L', 'XL'],
            'variants' => $variantsData,
            'image' => $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
            'hoverImage' => $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null,
            'description' => $product->description,
            'multipleImages' => $multipleImages,
            'colors' => $product->colors->pluck('color')->unique()->values()->toArray() ?: ['black', 'blue', 'red', 'green'],
            'customizable' => false,
            'slug' => $product->slug,
            'total_stock' => $totalStock
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'Products fetched successfully',
        'data' => $products
    ]);
}

/**
 * Search products by name, description, category, or gender.
 */
public function search(Request $request)
{
    $query = $request->get('query', '');

    if (empty($query)) {
        return response()->json([
            'success' => false,
            'message' => 'Search query is required',
            'data' => []
        ], 400);
    }

    $products = Products::with([
        'category:id,name,gender',
        'colors:id,product_id,color,images',
        'variants:id,product_id,color_id,size,stock,price,discount,total_price'
    ])
    ->select('id', 'category_id', 'name', 'main_image', 'zoomed_image', 'description', 'gender', 'slug')
    ->where(function ($q) use ($query) {
        $q->where('name', 'like', "%{$query}%")
          ->orWhere('description', 'like', "%{$query}%")
          ->orWhere('gender', 'like', "%{$query}%")
          ->orWhereHas('category', function ($cat) use ($query) {
              $cat->where('name', 'like', "%{$query}%");
          });
    })
    ->orderBy('created_at', 'desc')
    ->get();

    $formatted = $products->map(function ($product) {
        $colorImages = $product->colors->pluck('images')->flatten()->filter()->unique()->values()->toArray();

        $multipleImages = [];
        if (!empty($colorImages)) {
            $multipleImages = array_map(fn($img) => asset('storage/products/' . basename($img)), $colorImages);
        } else {
            $multipleImages = [
                $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null
            ];
        }

        $totalStock = $product->variants->sum('stock');

        $variantsData = $product->variants->map(function($v) {
            return [
                'id' => $v->id,
                'size' => $v->size,
                'stock' => $v->stock ?? 0,
                'color_id' => $v->color_id,
                'price' => $v->price ?? 0,
                'discount' => $v->discount ?? 0,
                'total_price' => $v->total_price ?? $v->price ?? 0
            ];
        })->toArray();

        $minPrice = $product->variants->min('total_price') ?? 0;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category->name ?? null,
            'price' => $minPrice,
            'total_price' => $minPrice,
            'gender' => $product->gender ?? 'unisex',
            'color' => $product->colors->first()->color ?? 'black',
            'sizes' => $product->variants->pluck('size')->unique()->values()->toArray() ?: ['S', 'M', 'L', 'XL'],
            'variants' => $variantsData,
            'image' => $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
            'hoverImage' => $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null,
            'description' => $product->description,
            'multipleImages' => $multipleImages,
            'colors' => $product->colors->pluck('color')->toArray() ?: ['black', 'blue', 'red', 'green'],
            'slug' => $product->slug,
            'total_stock' => $totalStock
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'Search results fetched successfully',
        'data' => $formatted
    ]);
}

/**
 * Submit a product review (Available only after order delivery).
 */
public function storeReview(Request $request, $id)
{
    try {
        $product = Products::findOrFail($id);
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Verify that the user has a delivered order containing this product
        $deliveredOrders = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->get();

        $hasPurchased = $deliveredOrders->contains(function ($order) use ($product) {
            $items = is_array($order->items) ? $order->items : (json_decode($order->items, true) ?: []);
            foreach ($items as $item) {
                if (isset($item['product_id']) && $item['product_id'] == $product->id) {
                    return true;
                }
                if (isset($item['product']['id']) && $item['product']['id'] == $product->id) {
                    return true;
                }
            }
            return false;
        });

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review products that have been delivered to you.'
            ], 403);
        }

        // Check if user has already reviewed this product
        $existing = Review::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product.'
            ], 422);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
            'image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reviews', 'public');
        }

        $review = Review::create([
            'product_id' => $product->id,
            'user_id'    => $user->id,
            'name'       => $user->name,
            'rating'     => $request->rating,
            'review'     => $request->review,
            'image'      => $imagePath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data'    => $review
        ], 201);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors'  => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error submitting review: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Check if the user is eligible to write a review.
 */
public function checkReviewEligibility($id)
{
    try {
        $product = Products::findOrFail($id);
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'eligible' => false,
                'message'  => 'Unauthorized'
            ]);
        }

        // Verify delivered order
        $deliveredOrders = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->get();

        $hasPurchased = $deliveredOrders->contains(function ($order) use ($product) {
            $items = is_array($order->items) ? $order->items : (json_decode($order->items, true) ?: []);
            foreach ($items as $item) {
                if (isset($item['product_id']) && $item['product_id'] == $product->id) {
                    return true;
                }
                if (isset($item['product']['id']) && $item['product']['id'] == $product->id) {
                    return true;
                }
            }
            return false;
        });

        // Check if already reviewed
        $alreadyReviewed = Review::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();

        return response()->json([
            'eligible'         => $hasPurchased && !$alreadyReviewed,
            'already_reviewed' => $alreadyReviewed,
            'purchased'        => $hasPurchased
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'eligible' => false,
            'message'  => 'Error: ' . $e->getMessage()
        ]);
    }
}

}