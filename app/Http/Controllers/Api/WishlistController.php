<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WishlistController extends Controller
{
    /**
     * Get all wishlist items for the current user or guest session
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->header('X-Session-ID') ?: $request->input('session_id');

            if ($user) {
                // Authenticated user
                $wishlistItems = Wishlist::with('product')
                    ->where('user_id', $user->id)
                    ->get();
            } else if ($sessionId) {
                // Guest user
                $wishlistItems = Wishlist::with('product')
                    ->where('session_id', $sessionId)
                    ->whereNull('user_id')
                    ->get();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID required for guest users',
                    'data' => []
                ], 400);
            }

            $formatted = $wishlistItems->map(function ($item) {
                $product = $item->product;
                $details = $item->product_details ?? [];

                if ($product) {
                    return [
                        'wishlist_id' => $item->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image' => $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                        'hoverImage' => $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null,
                        'selectedSize' => $details['selectedSize'] ?? '',
                        'selectedColor' => $details['selectedColor'] ?? '',
                        'quantity' => $details['quantity'] ?? 1,
                        'added_at' => $item->created_at
                    ];
                }
                return null;
            })->filter();

            return response()->json([
                'success' => true,
                'message' => 'Wishlist fetched successfully',
                'data' => $formatted->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to wishlist
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'session_id' => 'required_without:user_id|string'
            ]);

            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->input('session_id');
            $productId = $request->input('product_id');

            // Check if item already exists in wishlist
            $existingItem = null;
            if ($user) {
                $existingItem = Wishlist::where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->first();
            } else if ($sessionId) {
                $existingItem = Wishlist::where('session_id', $sessionId)
                    ->where('product_id', $productId)
                    ->whereNull('user_id')
                    ->first();
            }

            if ($existingItem) {
                // Update existing wishlist item
                $existingItem->update([
                    'product_details' => [
                        'selectedSize' => $request->input('selectedSize', ''),
                        'selectedColor' => $request->input('selectedColor', ''),
                        'quantity' => $request->input('quantity', 1),
                    ]
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Wishlist item updated successfully',
                    'data' => $existingItem
                ]);
            }

            // Create new wishlist item
            $wishlistData = [
                'product_id' => $productId,
                'product_details' => [
                    'selectedSize' => $request->input('selectedSize', ''),
                    'selectedColor' => $request->input('selectedColor', ''),
                    'quantity' => $request->input('quantity', 1),
                ]
            ];

            if ($user) {
                $wishlistData['user_id'] = $user->id;
            } else {
                $wishlistData['session_id'] = $sessionId;
            }

            $wishlistItem = Wishlist::create($wishlistData);

            return response()->json([
                'success' => true,
                'message' => 'Item added to wishlist successfully',
                'data' => $wishlistItem
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding to wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from wishlist
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->header('X-Session-ID') ?: $request->input('session_id');

            $query = Wishlist::where('product_id', $id);

            if ($user) {
                $query->where('user_id', $user->id);
            } else if ($sessionId) {
                $query->where('session_id', $sessionId)->whereNull('user_id');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID required for guest users'
                ], 400);
            }

            $wishlistItem = $query->first();

            if (!$wishlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wishlist item not found'
                ], 404);
            }

            $wishlistItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from wishlist successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing from wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear entire wishlist
     */
    public function clear(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->header('X-Session-ID') ?: $request->input('session_id');

            if ($user) {
                Wishlist::where('user_id', $user->id)->delete();
            } else if ($sessionId) {
                Wishlist::where('session_id', $sessionId)->whereNull('user_id')->delete();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID required for guest users'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Wishlist cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Merge guest wishlist with user wishlist after login
     */
    public function mergeWishlist(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->input('session_id');

            if (!$user || !$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User must be authenticated and session_id must be provided'
                ], 400);
            }

            // Get guest wishlist items
            $guestItems = Wishlist::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->get();

            foreach ($guestItems as $guestItem) {
                // Check if user already has this product in wishlist
                $existingItem = Wishlist::where('user_id', $user->id)
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if (!$existingItem) {
                    // Transfer guest item to user
                    $guestItem->update([
                        'user_id' => $user->id,
                        'session_id' => null
                    ]);
                } else {
                    // Delete duplicate guest item
                    $guestItem->delete();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Wishlist merged successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error merging wishlist: ' . $e->getMessage()
            ], 500);
        }
    }
}
