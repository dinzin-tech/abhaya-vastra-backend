<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\User;
use App\Models\Products;
use App\Models\CartCouponLog;
use App\Mail\CouponMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ActiveCartController extends Controller
{
    /**
     * Display real-time active user carts with filter options and sent counts.
     */
    public function index(Request $request)
    {
        $minAmount = $request->get('min_amount', 0);
        $userType  = $request->get('user_type', 'all'); // 'all', 'registered', 'guest'
        $productId = $request->get('product_id', '');
        $search    = strtolower(trim($request->get('search', '')));

        // Fetch products currently present in any active cart for the dropdown
        $cartProductIds = Cart::pluck('product_id')->unique()->filter()->toArray();
        $productsInCarts = Products::whereIn('id', $cartProductIds)->get(['id', 'name']);

        // Fetch all active non-empty cart items with product & user details
        $rawCarts = Cart::with(['user', 'product'])
            ->whereHas('product')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Fetch email log counts
        $logCounts = CartCouponLog::select('email', DB::raw('count(*) as total_sent'))
            ->groupBy('email')
            ->pluck('total_sent', 'email')
            ->toArray();

        // Group by user_id for registered users, and session_id for guests
        $grouped = $rawCarts->groupBy(function ($item) {
            return $item->user_id ? 'user_' . $item->user_id : 'session_' . $item->session_id;
        });

        $activeCarts = [];
        $totalPotentialRevenue = 0;

        foreach ($grouped as $key => $items) {
            $first = $items->first();
            $user = $first->user;

            // Product-wise filter
            if ($productId !== '') {
                $hasSelectedProduct = $items->contains(fn($item) => (string)$item->product_id === (string)$productId);
                if (!$hasSelectedProduct) continue;
            }

            $cartTotal = 0;
            $totalQuantity = 0;

            $formattedItems = [];
            foreach ($items as $item) {
                $price = $item->product ? ($item->product->total_price ?? $item->product->price ?? 0) : 0;
                $lineTotal = $price * $item->quantity;
                $cartTotal += $lineTotal;
                $totalQuantity += $item->quantity;

                $formattedItems[] = [
                    'id'           => $item->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product ? $item->product->name : 'Product #' . $item->product_id,
                    'image'        => $item->product ? ($item->product->main_image ? asset('storage/products/' . basename($item->product->main_image)) : asset('assets/images/placeholder.jpg')) : '',
                    'size'         => $item->selected_size,
                    'color'        => $item->selected_color,
                    // Combo size fields
                    'male_size'    => $item->male_size,
                    'female_size'  => $item->female_size,
                    'quantity'     => $item->quantity,
                    'price'        => $price,
                    'line_total'   => $lineTotal,
                ];
            }

            // Apply Filters
            if ($totalQuantity <= 0) continue;
            if ($minAmount > 0 && $cartTotal < $minAmount) continue;
            if ($userType === 'registered' && !$first->user_id) continue;
            if ($userType === 'guest' && $first->user_id) continue;

            $userName = $user ? $user->name : 'Guest User (' . substr($first->session_id, 0, 8) . ')';
            $userEmail = $user ? $user->email : null;
            $userPhone = $user ? $user->phone : null;

            if ($search !== '') {
                $match = str_contains(strtolower($userName), $search) ||
                         str_contains(strtolower($userEmail ?? ''), $search) ||
                         str_contains(strtolower($userPhone ?? ''), $search);
                if (!$match) continue;
            }

            $totalPotentialRevenue += $cartTotal;
            $lastUpdated = $items->max('updated_at');

            $activeCarts[] = [
                'key' => $key,
                'user_id' => $first->user_id,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'user_phone' => $userPhone,
                'is_guest' => !$first->user_id,
                'items' => $formattedItems,
                'total_items' => $totalQuantity,
                'cart_total' => $cartTotal,
                'last_updated' => $lastUpdated ? $lastUpdated->diffForHumans() : 'Recently',
                'updated_timestamp' => $lastUpdated ? $lastUpdated->timestamp : 0,
                'sent_count' => $userEmail ? ($logCounts[strtolower($userEmail)] ?? 0) : 0,
            ];
        }

        // Sort by most recently active
        usort($activeCarts, fn($a, $b) => $b['updated_timestamp'] <=> $a['updated_timestamp']);

        // Fetch active coupons for assigning
        $coupons = Coupon::where('status', true)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->get();

        return view('admin.modules.active_carts.index', compact(
            'activeCarts',
            'totalPotentialRevenue',
            'coupons',
            'productsInCarts',
            'minAmount',
            'userType',
            'productId',
            'search'
        ));
    }

    /**
     * Send coupon & email message directly to a single cart user.
     */
    public function sendCoupon(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'coupon_id' => 'required|exists:coupons,id',
            'custom_message' => 'nullable|string',
        ]);

        $email = strtolower($request->email);
        $coupon = Coupon::findOrFail($request->coupon_id);
        $user = User::where('email', $email)->first();

        // Assign coupon in pivot table if user exists
        if ($user) {
            $coupon->users()->syncWithoutDetaching([
                $user->id => [
                    'used' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
        }

        $customMessage = $request->custom_message ?: "We noticed you left some amazing items in your cart! Here is an exclusive coupon code to complete your order with extra savings.";

        try {
            Mail::to($email)->send(new CouponMail($coupon, $customMessage));

            // Record log entry
            CartCouponLog::create([
                'email' => $email,
                'coupon_id' => $coupon->id,
                'custom_message' => $customMessage,
                'sent_at' => now(),
            ]);

            $newCount = CartCouponLog::where('email', $email)->count();

            return response()->json([
                'success' => true,
                'message' => "Coupon '{$coupon->code}' sent to {$email}! Total times sent: {$newCount}",
                'sent_count' => $newCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed sending active cart coupon email: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk send coupon offer emails to multiple selected cart users.
     */
    public function bulkSendCoupon(Request $request)
    {
        $request->validate([
            'emails' => 'required|array|min:1',
            'emails.*' => 'required|email',
            'coupon_id' => 'required|exists:coupons,id',
            'custom_message' => 'nullable|string',
        ]);

        $emails = array_unique(array_map('strtolower', $request->emails));
        $coupon = Coupon::findOrFail($request->coupon_id);
        $customMessage = $request->custom_message ?: "We noticed you left some amazing items in your cart! Here is an exclusive coupon code to complete your order with extra savings.";

        $successCount = 0;
        $failedEmails = [];

        foreach ($emails as $email) {
            try {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $coupon->users()->syncWithoutDetaching([
                        $user->id => [
                            'used' => false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    ]);
                }

                Mail::to($email)->send(new CouponMail($coupon, $customMessage));

                CartCouponLog::create([
                    'email' => $email,
                    'coupon_id' => $coupon->id,
                    'custom_message' => $customMessage,
                    'sent_at' => now(),
                ]);

                $successCount++;
            } catch (\Exception $e) {
                Log::error("Bulk send failed for {$email}: " . $e->getMessage());
                $failedEmails[] = $email;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully sent coupon '{$coupon->code}' to {$successCount} cart user(s)!",
            'success_count' => $successCount,
            'failed_count' => count($failedEmails),
        ]);
    }
}
