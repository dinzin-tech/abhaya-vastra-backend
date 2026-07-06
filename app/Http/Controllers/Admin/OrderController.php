<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;

class OrderController extends Controller
{
    /**
     * Display the Orders listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.orders.list', [
            'q'      => $request->q,
            'offset' => $request->offset,
            'status' => $request->status,
            'name'  => $request->name,
            'phone' => $request->phone,
            'order_number' => $request->order_number
        ]);
    }

    /**
     * Fetch Orders rows for AJAX listing.
     */
    public function listOrders(Request $request)
    {
        $query = Order::with(['user', 'payment']);
        $offset = $request->offset ?? 10;

        if ($request->phone != null && $request->phone != '') {
            $query->where('phone', 'like', "%$request->phone%");
        }
        if ($request->name != null && $request->name != '') {
            $query->where('name', 'like', "%$request->name%");
        }

        if ($request->order_number != null && $request->order_number != '') {
            $query->where('order_number', 'like', "%$request->order_number%");
        }

        // Filter by status
        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.orders.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show order details.
     */
    public function show($id)
    {
        $order = Order::with(['user', 'payment'])->findOrFail($id);
        return view('admin.modules.orders.show', ['order' => $order]);
    }

    /**
     * Get order details as JSON for modal display.
     */
    public function getOrderDetails($id)
    {
        $order = Order::with(['user', 'payment'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($request->order_id);
        $oldStatus = $order->status;
        $order->status = $request->status;
        
        // If order is delivered, mark payment as completed for COD
        if ($request->status == 'delivered' && $order->payment_method == 'cod') {
            $order->payment_status = 'completed';
            
            // Update payment record
            if ($order->payment) {
                $order->payment->update([
                    'status' => 'completed',
                    'paid_at' => now()
                ]);
            }
        }

        // Handle cancellations
        if ($request->status == 'cancelled' && $oldStatus != 'cancelled') {
            // Restore wallet money & loyalty points
            if ($order->wallet_money_used > 0 || $order->loyalty_points_used > 0) {
                $wallet = \App\Models\Wallet::where('user_id', $order->user_id)->first();
                if ($wallet) {
                    if ($order->wallet_money_used > 0) {
                        $wallet->increment('wallet_balance', $order->wallet_money_used);
                        \App\Models\WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => 'credit',
                            'points' => 0,
                            'status' => 'completed',
                            'description' => 'Reverted ₹' . $order->wallet_money_used . ' due to cancelled order #' . $order->order_number,
                            'reference' => 'REVERT_' . $order->id,
                        ]);
                    }
                    if ($order->loyalty_points_used > 0) {
                        $wallet->increment('balance', $order->loyalty_points_used);
                        \App\Models\WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => 'credit',
                            'points' => $order->loyalty_points_used,
                            'status' => 'completed',
                            'description' => 'Reverted ' . $order->loyalty_points_used . ' points due to cancelled order #' . $order->order_number,
                            'reference' => 'REVERT_' . $order->id,
                        ]);
                    }
                }
            }

            // Revert coupon usage
            if (!empty($order->coupon_code)) {
                $coupon = \App\Models\Coupon::where('code', $order->coupon_code)->first();
                if ($coupon) {
                    $coupon->decrement('used_count');
                }
            }

            // Restore stock if payment was completed
            if ($order->payment_status === 'completed') {
                $this->restoreStock($order);
            }
        }
        
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    }

    /**
     * Delete an order.
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:orders,id'
        ]);

        $order = Order::findOrFail($request->id);
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }

    /**
     * Restore stock for returned order items
     */
    private function restoreStock($order)
    {
        try {
            foreach ($order->items as $item) {
                if (isset($item['product_id']) && isset($item['size'])) {
                    $variantQuery = \App\Models\ProductVariant::where('product_id', $item['product_id'])
                        ->where('size', $item['size']);

                    if (!empty($item['color'])) {
                        $color = \App\Models\ProductColor::where('color', $item['color'])->first();
                        if ($color) {
                            $variantQuery->where('color_id', $color->id);
                        }
                    }

                    $variant = $variantQuery->first();

                    if ($variant) {
                        $oldStock = $variant->stock;
                        $newStock = $oldStock + $item['quantity'];
                        $variant->update(['stock' => $newStock]);

                        \Log::info('Stock restored successfully (admin order cancelled)', [
                            'product_id' => $item['product_id'],
                            'variant_id' => $variant->id,
                            'size' => $item['size'],
                            'quantity_restored' => $item['quantity'],
                            'old_stock' => $oldStock,
                            'new_stock' => $newStock,
                            'order_id' => $order->id
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to restore stock on cancel: ' . $e->getMessage(), [
                'order_id' => $order->id
            ]);
        }
    }
}
