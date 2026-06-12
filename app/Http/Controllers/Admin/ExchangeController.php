<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderExchange;
use App\Models\Order;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ExchangeStatusMail;
use App\Models\ProductVariant;




class ExchangeController extends Controller
{
    protected $shiprocketService;

    public function __construct(ShiprocketService $shiprocketService)
    {
        $this->shiprocketService = $shiprocketService;
    }

    /**
     * Display a listing of exchange requests
     */
    public function index(Request $request)
    {
        return view('admin.modules.exchanges.list', [
            'q' => $request->q,
            'offset' => $request->offset,
            'status' => $request->status,
            'name' => $request->name,
            'phone' => $request->phone,
            'order_number' => $request->order_number
        ]);
    }

    /**
     * Fetch exchange requests for AJAX listing
     */
    public function listExchanges(Request $request)
    {
        $query = OrderExchange::with(['order.user', 'user']);
        $offset = $request->offset ?? 10;

        if (!empty($request->phone)) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('phone', 'like', "%{$request->phone}%");
            });
        }

        if (!empty($request->name)) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->name}%");
            });
        }

        if (!empty($request->order_number)) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->order_number}%");
            });
        }

        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows' => view('admin.modules.exchanges.list_rows', ['items' => $items])->render(),
            'items' => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Get exchange request details as JSON
     */
    public function getExchangeDetails($id)
    {
        $exchange = OrderExchange::with(['order.user', 'user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $exchange
        ]);
    }

    /**
     * Update exchange status
     */
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $request->validate([
                'status' => 'required|in:pending,approved,rejected,pickup_scheduled,picked_up,exchange_shipped,completed,cancelled',
                'admin_note' => 'nullable|string'
            ]);

            $exchange = OrderExchange::with('order')->findOrFail($id);
            $oldStatus = $exchange->status;
            $newStatus = $request->status;

            // Update exchange status
            $exchange->update([
                'status' => $newStatus,
                'admin_note' => $request->admin_note,
                'admin_updated_at' => now()
            ]);

            // Handle approved exchange - update variant quantities
            if ($newStatus === 'approved') {
                // Decrease quantity of the new variant
                $newVariant = ProductVariant::where('product_id', $exchange->product_id)
                    ->where('size', $exchange->exchange_size)
                    ->when($exchange->exchange_color, function($query) use ($exchange) {
                        $query->whereHas('color', function($q) use ($exchange) {
                            $q->where('color', $exchange->exchange_color);
                        });
                    })
                    ->first();

                if ($newVariant) {
                    if ($newVariant->stock > 0) {
                        $newVariant->decrement('stock');
                    } else {
                        throw new \Exception('Insufficient stock for the selected variant');
                    }
                }

                // Increase quantity of the original variant (the one being returned)
                $originalVariant = ProductVariant::where('product_id', $exchange->product_id)
                    ->where('size', $exchange->original_size)
                    ->when($exchange->original_color, function($query) use ($exchange) {
                        $query->whereHas('color', function($q) use ($exchange) {
                            $q->where('color', $exchange->original_color);
                        });
                    })
                    ->first();

                if ($originalVariant) {
                    $originalVariant->increment('stock');
                }
            }
            // Handle rejected exchange - process refund
            elseif ($newStatus === 'rejected' && $exchange->payment_status === 'paid') {
                // Process refund here (implement your refund logic)
                // This is a placeholder - replace with your actual payment gateway's refund API call
                $refundAmount = $exchange->exchange_amount ?? 100; // Default to 100 if not set
                $refundId = 'REF-' . time() . '-' . $exchange->id;
                
                // Update payment status to refunded
                $exchange->update([
                    'payment_status' => 'refunded',
                    'refund_id' => $refundId,
                    'refund_amount' => $refundAmount,
                    'refunded_at' => now()
                ]);
                
                // Send refund email
                try {
                    Mail::to($exchange->order->email)->send(new \App\Mail\ExchangeRejectedMail([
                        'exchange' => $exchange,
                        'refundAmount' => $refundAmount,
                        'refundId' => $refundId
                    ]));
                } catch (\Exception $e) {
                    \Log::error('Failed to send exchange rejection email: ' . $e->getMessage());
                }
            }

            // Send status update email
            try {
                Mail::to($exchange->order->email)->queue(new ExchangeStatusMail($exchange->load('order')));
            } catch (\Exception $e) {
                \Log::error('Failed to send exchange status email: ' . $e->getMessage());
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Exchange status updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Schedule pickup for original product via Shiprocket
     */
    public function schedulePickup(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $exchange = OrderExchange::with('order')->findOrFail($id);

            // Check if exchange is approved and payment is completed
            if ($exchange->status !== 'approved' && $exchange->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Exchange must be in approved or pending status'
                ], 422);
            }

            if ($exchange->payment_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment must be completed before scheduling pickup'
                ], 422);
            }

            // Create return order in Shiprocket for pickup
            $order = $exchange->order;
            
            // Get first pickup location from Shiprocket
            $pickupLocations = $this->shiprocketService->getPickupLocations();
            $pickupLocation = $pickupLocations['data']['shipping_address'][0]['pickup_location'] ?? 'Primary';
            
            $pickupOrderData = [
                'order_id' => 'EXC-PICKUP-' . $exchange->id . '-' . time(),
                'order_date' => now()->format('Y-m-d H:i'),
                'pickup_location' => $pickupLocation,
                'channel_id' => '',
                'comment' => 'Exchange Pickup - Original Product',
                'billing_customer_name' => $order->name,
                'billing_last_name' => '',
                'billing_address' => $order->address,
                'billing_address_2' => '',
                'billing_city' => $order->city,
                'billing_pincode' => $order->zip,
                'billing_state' => $order->state,
                'billing_country' => 'India',
                'billing_email' => $order->email,
                'billing_phone' => $order->phone,
                'shipping_is_billing' => true,
                'shipping_customer_name' => '',
                'shipping_last_name' => '',
                'shipping_address' => '',
                'shipping_address_2' => '',
                'shipping_city' => '',
                'shipping_pincode' => '',
                'shipping_country' => '',
                'shipping_state' => '',
                'shipping_email' => '',
                'shipping_phone' => '',
                'order_items' => [[
                    'name' => 'Exchange Return Product',
                    'sku' => 'EXC-RETURN-' . $exchange->id,
                    'units' => 1,
                    'selling_price' => '1',
                    'discount' => '0',
                    'tax' => '0',
                    'hsn' => ''
                ]],
                'payment_method' => 'Prepaid',
                'shipping_charges' => '0',
                'giftwrap_charges' => '0',
                'transaction_charges' => '0',
                'total_discount' => '0',
                'sub_total' => 1,
                'length' => 10,
                'breadth' => 10,
                'height' => 10,
                'weight' => 0.5
            ];

            $shiprocketResponse = $this->shiprocketService->createOrder($pickupOrderData);

            if (isset($shiprocketResponse['order_id'])) {
                // Generate AWB for pickup
                $awbResponse = $this->shiprocketService->generateAWB(
                    $shiprocketResponse['shipment_id'],
                    $shiprocketResponse['order_id']
                );

                $exchange->update([
                    'shiprocket_pickup_order_id' => $shiprocketResponse['order_id'],
                    'shiprocket_pickup_shipment_id' => $shiprocketResponse['shipment_id'],
                    'shiprocket_pickup_awb_code' => $awbResponse['awb_code'] ?? null,
                    'shiprocket_pickup_courier_name' => $awbResponse['courier_name'] ?? null,
                    'pickup_scheduled_at' => now(),
                    'status' => 'pickup_scheduled'
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pickup scheduled successfully',
                    'data' => $exchange
                ]);
            }

            throw new \Exception('Failed to create Shiprocket pickup order');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling pickup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Schedule delivery for exchanged product via Shiprocket
     */
    public function scheduleDelivery(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $exchange = OrderExchange::with('order')->findOrFail($id);

            // Check if pickup is completed
            if ($exchange->status !== 'picked_up') {
                return response()->json([
                    'success' => false,
                    'message' => 'Original product must be picked up before scheduling delivery'
                ], 422);
            }

            // Create delivery order in Shiprocket
            $order = $exchange->order;
            
            // Get first pickup location from Shiprocket
            $pickupLocations = $this->shiprocketService->getPickupLocations();
            $pickupLocation = $pickupLocations['data']['shipping_address'][0]['pickup_location'] ?? 'Primary';
            
            $deliveryOrderData = [
                'order_id' => 'EXC-DELIVERY-' . $exchange->id . '-' . time(),
                'order_date' => now()->format('Y-m-d H:i'),
                'pickup_location' => $pickupLocation,
                'channel_id' => '',
                'comment' => 'Exchange Delivery - New Product (Size: ' . $exchange->exchange_size . ')',
                'billing_customer_name' => $order->name,
                'billing_last_name' => '',
                'billing_address' => $order->address,
                'billing_address_2' => '',
                'billing_city' => $order->city,
                'billing_pincode' => $order->zip,
                'billing_state' => $order->state,
                'billing_country' => 'India',
                'billing_email' => $order->email,
                'billing_phone' => $order->phone,
                'shipping_is_billing' => true,
                'shipping_customer_name' => '',
                'shipping_last_name' => '',
                'shipping_address' => '',
                'shipping_address_2' => '',
                'shipping_city' => '',
                'shipping_pincode' => '',
                'shipping_country' => '',
                'shipping_state' => '',
                'shipping_email' => '',
                'shipping_phone' => '',
                'order_items' => [[
                    'name' => 'Exchange Product - ' . $exchange->exchange_size . ($exchange->exchange_color ? ' - ' . $exchange->exchange_color : ''),
                    'sku' => 'PROD-' . $exchange->product_id,
                    'units' => 1,
                    'selling_price' => $exchange->exchange_amount ?? 100, // Use actual exchange amount or default to 100
                    'discount' => '0',
                    'tax' => '0',
                    'hsn' => ''
                ]],
                'payment_method' => 'Prepaid',
                'shipping_charges' => '0',
                'giftwrap_charges' => '0',
                'transaction_charges' => '0',
                'total_discount' => '0',
                'sub_total' => $exchange->exchange_amount ?? 100, // Use actual exchange amount or default to 100
                'length' => 10,
                'breadth' => 10,
                'height' => 10,
                'weight' => 0.5
            ];

            $shiprocketResponse = $this->shiprocketService->createOrder($deliveryOrderData);

            if (isset($shiprocketResponse['order_id'])) {
                // Generate AWB for delivery
                $awbResponse = $this->shiprocketService->generateAWB(
                    $shiprocketResponse['shipment_id'],
                    $shiprocketResponse['order_id']
                );

                $exchange->update([
                    'shiprocket_delivery_order_id' => $shiprocketResponse['order_id'],
                    'shiprocket_delivery_shipment_id' => $shiprocketResponse['shipment_id'],
                    'shiprocket_delivery_awb_code' => $awbResponse['awb_code'] ?? null,
                    'shiprocket_delivery_courier_name' => $awbResponse['courier_name'] ?? null,
                    'delivery_scheduled_at' => now(),
                    'status' => 'exchange_shipped'
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Delivery scheduled successfully',
                    'data' => $exchange
                ]);
            }

            throw new \Exception('Failed to create Shiprocket delivery order');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark exchange as completed
     */
    public function markCompleted(Request $request, $id)
    {
        try {
            $exchange = OrderExchange::findOrFail($id);

            $exchange->update([
                'status' => 'completed',
                'delivered_at' => now(),
                'admin_updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exchange marked as completed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error completing exchange: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an exchange record
     */
    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:order_exchanges,id'
            ]);

            $exchange = OrderExchange::findOrFail($request->id);
            $exchange->delete();

            return response()->json([
                'success' => true,
                'message' => 'Exchange deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting exchange: ' . $e->getMessage()
            ], 500);
        }
    }
}
