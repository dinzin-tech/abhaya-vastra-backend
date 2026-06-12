<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get all cart items for the current user or guest session
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->header('X-Session-ID') ?: $request->input('session_id');

            if ($user) {
                // Authenticated user
                $cartItems = Cart::with('product')
                    ->where('user_id', $user->id)
                    ->get();
            } else if ($sessionId) {
                // Guest user
                $cartItems = Cart::with('product')
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

            $formatted = $cartItems->map(function ($item) {
                $product = $item->product;

                if ($product) {
                    // Get price from variant based on size and color
                    $price = 0;
                    $totalPrice = 0;
                    
                    if ($item->selected_size) {
                        // Find variant by size and color
                        $variantQuery = $product->variants()->where('size', $item->selected_size);
                        
                        if ($item->selected_color) {
                            // If color is selected, find the color ID first
                            $color = \App\Models\ProductColor::where('color', $item->selected_color)
                                ->where('product_id', $product->id)
                                ->first();
                            
                            if ($color) {
                                $variantQuery->where('color_id', $color->id);
                            }
                        }
                        
                        $variant = $variantQuery->first();
                        
                        if ($variant) {
                            $price = $variant->price ?? 0;
                            $totalPrice = $variant->total_price ?? $variant->price ?? 0;
                        }
                    } else {
                        // If no size selected, get first variant price as fallback
                        $firstVariant = $product->variants()->first();
                        if ($firstVariant) {
                            $price = $firstVariant->price ?? 0;
                            $totalPrice = $firstVariant->total_price ?? $firstVariant->price ?? 0;
                        }
                    }

                    return [
                        'cart_id' => $item->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => $totalPrice, // Use total_price (after discount) for display
                        'original_price' => $price,
                        'image' => $product->main_image ? asset('storage/products/' . basename($product->main_image)) : null,
                        'hoverImage' => $product->zoomed_image ? asset('storage/products/' . basename($product->zoomed_image)) : null,
                        'selectedSize' => $item->selected_size,
                        'selectedColor' => $item->selected_color,
                        'quantity' => $item->quantity,
                        'cartKey' => $item->selected_size ? "{$product->id}-{$item->selected_size}" : (string)$product->id,
                        'added_at' => $item->created_at
                    ];
                }
                return null;
            })->filter();

            return response()->json([
                'success' => true,
                'message' => 'Cart fetched successfully',
                'data' => $formatted->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'integer|min:1',
                'session_id' => 'required_without:user_id|string'
            ]);

            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->input('session_id');
            $productId = $request->input('product_id');
            $selectedSize = $request->input('selectedSize', '');
            $selectedColor = $request->input('selectedColor', '');
            $quantity = $request->input('quantity', 1);

            // Check if item already exists in cart
            $existingItem = null;
            if ($user) {
                $existingItem = Cart::where('user_id', $user->id)
                    ->where('product_id', $productId)
                    ->where('selected_size', $selectedSize)
                    ->first();
            } else if ($sessionId) {
                $existingItem = Cart::where('session_id', $sessionId)
                    ->where('product_id', $productId)
                    ->where('selected_size', $selectedSize)
                    ->whereNull('user_id')
                    ->first();
            }

            if ($existingItem) {
                // Update existing cart item quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $quantity,
                    'selected_color' => $selectedColor,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Cart item quantity updated successfully',
                    'data' => $existingItem
                ]);
            }

            // Create new cart item
            $cartData = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'selected_size' => $selectedSize,
                'selected_color' => $selectedColor,
                'product_details' => [
                    'selectedSize' => $selectedSize,
                    'selectedColor' => $selectedColor,
                ]
            ];

            if ($user) {
                $cartData['user_id'] = $user->id;
            } else {
                $cartData['session_id'] = $sessionId;
            }

            $cartItem = Cart::create($cartData);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully',
                'data' => $cartItem
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
                'message' => 'Error adding to cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->header('X-Session-ID') ?: $request->input('session_id');

            $query = Cart::where('id', $id);

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

            $cartItem = $query->first();

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $cartItem->update(['quantity' => $request->input('quantity')]);

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => $cartItem
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->header('X-Session-ID') ?: $request->input('session_id');

            $query = Cart::where('id', $id);

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

            $cartItem = $query->first();

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart item not found'
                ], 404);
            }

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing from cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            $sessionId = $request->header('X-Session-ID') ?: $request->input('session_id');

            if ($user) {
                Cart::where('user_id', $user->id)->delete();
            } else if ($sessionId) {
                Cart::where('session_id', $sessionId)->whereNull('user_id')->delete();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID required for guest users'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Merge guest cart with user cart after login
     */
    public function mergeCart(Request $request)
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

            // Get guest cart items
            $guestItems = Cart::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->get();

            foreach ($guestItems as $guestItem) {
                // Check if user already has this product with same size in cart
                $existingItem = Cart::where('user_id', $user->id)
                    ->where('product_id', $guestItem->product_id)
                    ->where('selected_size', $guestItem->selected_size)
                    ->first();

                if ($existingItem) {
                    // Merge quantities
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $guestItem->quantity
                    ]);
                    // Delete guest item
                    $guestItem->delete();
                } else {
                    // Transfer guest item to user
                    $guestItem->update([
                        'user_id' => $user->id,
                        'session_id' => null
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart merged successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error merging cart: ' . $e->getMessage()
            ], 500);
        }
    }
}
