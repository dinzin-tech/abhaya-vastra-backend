<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Services\ShiprocketService;
use Illuminate\Support\Facades\Log;

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

        // Filter by Shiprocket Deliveries
        if ($request->shiprocket == '1') {
            $query->whereNotNull('shiprocket_order_id');
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

    // ─────────────────────────────────────────────────────────────────
    // Shiprocket Administration Methods
    // ─────────────────────────────────────────────────────────────────

    /**
     * Create a shipment in Shiprocket for an order.
     */
    public function shiprocketCreateShipment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->shiprocket_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment already exists for this order in Shiprocket.'
            ]);
        }

        try {
            $shiprocketService = app(ShiprocketService::class);
            $prepared = $shiprocketService->prepareOrderItems($order->items);

            $shiprocketOrderData = [
                'order_id'               => $order->order_number,
                'order_date'             => $order->created_at->format('Y-m-d H:i'),
                'pickup_location'        => config('services.shiprocket.pickup_location', 'Home'),
                'channel_id'             => '',
                'comment'                => 'Order from Admin Panel ' . config('app.name'),
                'billing_customer_name'  => $order->name,
                'billing_last_name'      => '',
                'billing_address'        => $order->address,
                'billing_address_2'      => '',
                'billing_city'           => $order->city,
                'billing_pincode'        => $order->zip,
                'billing_state'          => $order->state ?: 'Karnataka',
                'billing_country'        => 'India',
                'billing_email'          => $order->email,
                'billing_phone'          => $order->phone,
                'shipping_is_billing'    => true,
                'shipping_customer_name' => '',
                'shipping_last_name'     => '',
                'shipping_address'       => '',
                'shipping_address_2'     => '',
                'shipping_city'          => '',
                'shipping_pincode'       => '',
                'shipping_country'       => '',
                'shipping_state'         => '',
                'shipping_email'         => '',
                'shipping_phone'         => '',
                'order_items'            => $prepared['items'],
                'payment_method'         => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
                'shipping_charges'       => (float) ($order->shipping_charge ?? 0),
                'giftwrap_charges'       => 0,
                'transaction_charges'    => 0,
                'total_discount'         => (float) ($order->discount ?? 0),
                'sub_total'              => (float) $order->subtotal,
                'length'                 => 30,
                'breadth'                => 20,
                'height'                 => 10,
                'weight'                 => $prepared['total_weight'],
            ];

            Log::info('📦 Admin: Creating Shiprocket order adhoc', ['order_id' => $order->order_number]);
            $result = $shiprocketService->createOrder($shiprocketOrderData);

            if (!empty($result['order_id'])) {
                $order->update([
                    'shiprocket_order_id'    => $result['order_id'],
                    'shiprocket_shipment_id' => $result['shipment_id'] ?? null,
                    'status'                 => 'processing',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Shiprocket shipment created successfully.',
                    'data'    => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create shipment. Invalid API response.',
                'response'=> $result
            ]);
        } catch (\Exception $e) {
            Log::error('Admin Shiprocket Create Shipment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Shiprocket Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available couriers and rates for checking serviceability.
     */
    public function shiprocketGetCouriers($order_id)
    {
        $order = Order::findOrFail($order_id);

        try {
            $shiprocketService = app(ShiprocketService::class);
            $pickupPincode = config('services.shiprocket.pickup_pincode', '560064');
            $deliveryPincode = $order->zip;
            $prepared = $shiprocketService->prepareOrderItems($order->items);
            $weight = $prepared['total_weight'];
            $cod = $order->payment_method === 'cod' ? 1 : 0;

            $result = $shiprocketService->checkServiceability($pickupPincode, $deliveryPincode, $weight, $cod);

            if (!empty($result['data']['available_courier_companies'])) {
                return response()->json([
                    'success'  => true,
                    'couriers' => $result['data']['available_courier_companies']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No courier companies available for this pincode.',
                'response'=> $result
            ]);
        } catch (\Exception $e) {
            Log::error('Admin Shiprocket Get Couriers failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking serviceability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign selected courier AWB and schedule package pickup.
     */
    public function shiprocketAssignAwb(Request $request)
    {
        $request->validate([
            'order_id'   => 'required|exists:orders,id',
            'courier_id' => 'required|integer'
        ]);

        $order = Order::findOrFail($request->order_id);

        if (!$order->shiprocket_shipment_id) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not created yet. Please create shipment first.'
            ]);
        }

        try {
            $shiprocketService = app(ShiprocketService::class);
            
            // Assign AWB
            $awbResult = $shiprocketService->assignAwb($order->shiprocket_shipment_id, $request->courier_id);

            if (!empty($awbResult['response']['data']['awb_code'])) {
                $awbCode = $awbResult['response']['data']['awb_code'];
                $courierName = $awbResult['response']['data']['courier_name'] ?? 'Shiprocket Courier';

                $order->update([
                    'shiprocket_awb_code'     => $awbCode,
                    'shiprocket_courier_name' => $courierName,
                    'status'                  => 'shipped'
                ]);

                // Generate Pickup
                try {
                    $shiprocketService->generatePickup($order->shiprocket_shipment_id);
                    $pickupMsg = 'AWB assigned and pickup scheduled successfully.';
                } catch (\Exception $pe) {
                    $pickupMsg = 'AWB assigned successfully, but pickup scheduling failed: ' . $pe->getMessage();
                }

                return response()->json([
                    'success' => true,
                    'message' => $pickupMsg,
                    'data'    => [
                        'awb_code'     => $awbCode,
                        'courier_name' => $courierName
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign AWB. Shiprocket response invalid.',
                'response'=> $awbResult
            ]);
        } catch (\Exception $e) {
            Log::error('Admin Shiprocket Assign AWB failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shipment label URL.
     */
    public function shiprocketGenerateLabel($order_id)
    {
        $order = Order::findOrFail($order_id);

        if (!$order->shiprocket_shipment_id) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not created yet.'
            ]);
        }

        try {
            $shiprocketService = app(ShiprocketService::class);
            $result = $shiprocketService->generateLabel([$order->shiprocket_shipment_id]);

            if (!empty($result['label_url'])) {
                return response()->json([
                    'success'   => true,
                    'label_url' => $result['label_url']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate label.',
                'response'=> $result
            ]);
        } catch (\Exception $e) {
            Log::error('Admin Shiprocket Generate Label failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get manifest details.
     */
    public function shiprocketGenerateManifest($order_id)
    {
        $order = Order::findOrFail($order_id);

        if (!$order->shiprocket_shipment_id) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not created yet.'
            ]);
        }

        try {
            $shiprocketService = app(ShiprocketService::class);
            $result = $shiprocketService->generateManifest([$order->shiprocket_shipment_id]);

            if (!empty($result['manifest_url'])) {
                return response()->json([
                    'success'      => true,
                    'manifest_url' => $result['manifest_url']
                ]);
            }

            return response()->json([
                'success' => true,
                'data'    => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Admin Shiprocket Generate Manifest failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel shipment.
     */
    public function shiprocketCancelShipment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::findOrFail($request->order_id);

        if (!$order->shiprocket_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'No active Shiprocket order found for cancellation.'
            ]);
        }

        try {
            $shiprocketService = app(ShiprocketService::class);
            
            if ($order->shiprocket_awb_code) {
                $result = $shiprocketService->cancelShipment([$order->shiprocket_awb_code]);
            } else {
                $result = $shiprocketService->cancelOrder($order->shiprocket_order_id);
            }

            $order->update([
                'shiprocket_order_id'     => null,
                'shiprocket_shipment_id'  => null,
                'shiprocket_awb_code'     => null,
                'shiprocket_courier_name' => null,
                'shiprocket_tracking_url' => null,
                'status'                  => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shipment cancelled successfully and order reset to Pending status.',
                'data'    => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Admin Shiprocket Cancel Shipment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling shipment: ' . $e->getMessage()
            ], 500);
        }
    }
}
