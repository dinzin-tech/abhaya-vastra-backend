<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customized;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomizedProductController extends Controller
{
    /**
     * Get all customized products
     */
    public function index()
    {
        try {
            $products = Customized::select('id', 'title', 'description', 'images', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($product) {
                    $images = json_decode($product->images, true) ?? [];
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'description' => $product->description,
                        'image' => !empty($images[0]) ? asset('storage/' . $images[0]) : null,
                        'hoverImage' => !empty($images[1]) ? asset('storage/' . $images[1]) : null,
                        'customizable' => true,
                        'created_at' => $product->created_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single customized product
     */
    public function show($id)
    {
        try {
            $product = Customized::findOrFail($id);
            $images = json_decode($product->images, true) ?? [];
            
            $productData = [
                'id' => $product->id,
                'title' => $product->title,
                'description' => $product->description,
                'images' => array_map(function($image) {
                    return asset('storage/' . $image);
                }, $images),
                'customizable' => true,
                'created_at' => $product->created_at->format('Y-m-d H:i:s')
            ];

            return response()->json([
                'success' => true,
                'data' => $productData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all customized products with their images and logos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCustomizedProducts()
    {
        try {
            $products = Customized::all();
            
            $formattedProducts = $products->map(function($product) {
                $images = json_decode($product->images, true) ?? [];
                $logos = json_decode($product->logos, true) ?? [];
                
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'description' => $product->description,
                    'images' => array_map(function($image) {
                        return asset('storage/' . $image);
                    }, $images),
                    'logos' => array_map(function($logo) {
                        return asset('storage/' . $logo);
                    }, $logos),
                    'customizable' => true,
                    'created_at' => $product->created_at->format('Y-m-d H:i:s')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedProducts
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
