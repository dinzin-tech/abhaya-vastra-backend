<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\RazorpayController;
use App\Models\OrderReturn;
use App\Models\User;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\ProductColor;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReturnStatusMail;
use Illuminate\Support\Facades\Storage;

class ReturnController extends Controller
{
    /**
     * Display a listing of return requests with AJAX support
     */
    public function index(Request $request)
    {
        return view('admin.modules.returns.list', [
            'q' => $request->q,
            'offset' => $request->offset,
            'status' => $request->status,
            'name' => $request->name,
            'phone' => $request->phone,
            'order_number' => $request->order_number
        ]);
    }

    /**
     * Fetch return requests for AJAX listing.
     */
    public function listReturns(Request $request)
    {
        $query = OrderReturn::with(['order.user', 'user']);
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
            'rows' => view('admin.modules.returns.list_rows', ['items' => $items])->render(),
            'items' => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Get return request details as JSON for modal display.
     */
    public function getReturnDetails($id)
    {
        $return = OrderReturn::with(['order.user', 'user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $return
        ]);
    }

    /**
     * Display the specified return request.
     */
    public function show($id)
    {
        $return = OrderReturn::with(['order.user', 'user'])->findOrFail($id);
        return view('admin.returns.show', compact('return'));
    }

    /**
     * Update the specified return request status.
     */
    public function update(Request $request, $id)
    {
        $return = OrderReturn::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed',
            'admin_note' => 'nullable|string|max:1000'
        ]);

        $oldStatus = $return->status;
        $return->status = $request->status;
        $return->admin_note = $request->admin_note;
        $return->admin_updated_at = now();

        // Handle Shiprocket return pickup when return is approved
        if ($request->status === 'approved' && $oldStatus !== 'approved') {
            \Log::info('✅ Return approved for return ID: ' . $return->id);
            
            // Create Shiprocket return pickup (customer to seller)
            // Product will be picked up from customer and delivered to warehouse
            $this->createShiprocketReturnPickup($return);
            
            \Log::info('📋 Return approved. Shiprocket pickup scheduled. Refund will be processed when status is marked as "completed".');
        }
        
        // Handle refund and stock increase when return is completed (product received at warehouse)
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            \Log::info('🔄 Starting refund and stock update for return ID: ' . $return->id);
            
            // Increase stock for returned items (product is back in warehouse)
            $this->increaseStock($return->order);
            
            // Process refund
            try {
                $payment = Payment::where('order_id', $return->order_id)->first();

                if (!$payment) {
                    \Log::error('❌ No payment found for order_id: ' . $return->order_id);
                } elseif (!$payment->razorpay_payment_id) {
                    \Log::error('❌ No razorpay_payment_id for payment ID: ' . $payment->id);
                } elseif ($payment->status !== 'completed') {
                    \Log::error('❌ Payment status is not completed: ' . $payment->status);
                } else {
                    $order = $return->order;
                    $shippingCharge = $order->shipping_charge ?? ($order->subtotal * 0.1);
                    $refundAmount = $order->total - $shippingCharge;
                    
                    \Log::info('💰 Refund amount calculated', [
                        'order_total' => $order->total,
                        'shipping_charge' => $shippingCharge,
                        'refund_amount' => $refundAmount
                    ]);

                    $razorpayController = new RazorpayController();
                    $refundResponse = $razorpayController->processRefund(new Request([
                        'payment_id' => $payment->razorpay_payment_id,
                        'amount' => $refundAmount,
                        'return_id' => $return->id,
                    ]));
                    
                    \Log::info('📤 Refund response received', [
                        'status_code' => $refundResponse->getStatusCode(),
                        'content' => $refundResponse->getContent()
                    ]);

                    if ($refundResponse->getStatusCode() === 200) {
                        $refundData = json_decode($refundResponse->getContent(), true);
                        if (!empty($refundData['success'])) {
                            $return->refund_processed = true;
                            $return->refund_amount = $refundAmount;
                            $return->refund_id = $refundData['data']['refund_id'];
                            \Log::info('✅ Refund processed successfully', ['refund_id' => $refundData['data']['refund_id']]);
                        } else {
                            \Log::error('❌ Refund API returned success=false', ['response' => $refundData]);
                        }
                    } else {
                        \Log::error('❌ Refund API returned non-200 status', [
                            'status' => $refundResponse->getStatusCode(),
                            'response' => $refundResponse->getContent()
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('❌ Exception in refund process for return ID ' . $return->id, [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $return->save();

        // Send email notification to user
        try {
            $user = User::find($return->user_id);
            if ($user) {
                Mail::to($user->email)->queue(new ReturnStatusMail($return, $oldStatus));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send return status email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Return status updated successfully'
        ]);
    }

    /**
     * Remove the specified return request from storage.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:order_returns,id'
        ]);

        $return = OrderReturn::findOrFail($request->id);

        // Delete associated images from storage if they exist
        if ($return->images && is_array($return->images)) {
            foreach ($return->images as $imageUrl) {
                $filename = basename($imageUrl);
                $path = 'returns/' . $filename;
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        $return->delete();

        return response()->json([
            'success' => true,
            'message' => 'Return request deleted successfully'
        ]);
    }

    /**
     * Create Shiprocket return pickup when return is approved
     * Shiprocket will pick up from customer and deliver to seller
     */
    private function createShiprocketReturnPickup($return)
    {
        try {
            $order = $return->order;
            
            // Check if order has Shiprocket details
            if (!$order->shiprocket_order_id) {
                \Log::warning('⚠️ Order does not have Shiprocket order ID, skipping return pickup', [
                    'order_id' => $order->id
                ]);
                return;
            }

            $shiprocketService = new ShiprocketService();

            // Prepare return order items
            $returnItems = [];
            $totalWeight = 0;

            foreach ($order->items as $item) {
                $itemWeight = 0.5; // Default weight
                
                if (isset($item['product_id']) && isset($item['size']) && isset($item['color'])) {
                    $color = ProductColor::where('color', $item['color'])->first();
                    
                    if ($color) {
                        $variant = ProductVariant::where('product_id', $item['product_id'])
                            ->where('color_id', $color->id)
                            ->where('size', $item['size'])
                            ->first();
                        
                        if ($variant && $variant->weight) {
                            $itemWeight = (float) $variant->weight;
                        }
                    }
                }
                
                $returnItems[] = [
                    'name' => $item['name'],
                    'sku' => $item['id'] ?? 'SKU-' . $item['id'],
                    'units' => $item['quantity'],
                    'selling_price' => $item['price'],
                    'discount' => 0,
                    'tax' => 0,
                    'hsn' => '',
                ];

                $totalWeight += ($item['quantity'] * $itemWeight);
            }

            // Prepare Shiprocket return order data
            // Pickup from customer, delivery to seller warehouse
            $returnOrderData = [
                'order_id' => $order->order_number . '-RETURN-' . $return->id,
                'order_date' => now()->format('Y-m-d H:i'),
                'channel_id' => '',
                'comment' => 'Return pickup for order ' . $order->order_number,
                
                // Customer address - WHERE TO PICK UP FROM
                'pickup_customer_name' => $order->name,
                'pickup_last_name' => '',
                'pickup_address' => $order->address,
                'pickup_address_2' => '',
                'pickup_city' => $order->city,
                'pickup_pincode' => $order->zip,
                'pickup_state' => $order->state ?? '',
                'pickup_country' => 'India',
                'pickup_email' => $order->email,
                'pickup_phone' => $order->phone,
                
                // Seller warehouse address - WHERE TO DELIVER TO (Shiprocket uses shipping_* for destination)
                'shipping_customer_name' => config('services.shiprocket.warehouse_name', 'Warehouse'),
                'shipping_last_name' => '',
                'shipping_address' => config('services.shiprocket.warehouse_address', ''),
                'shipping_address_2' => '',
                'shipping_city' => config('services.shiprocket.warehouse_city', ''),
                'shipping_pincode' => config('services.shiprocket.pickup_pincode'),
                'shipping_state' => config('services.shiprocket.warehouse_state', ''),
                'shipping_country' => 'India',
                'shipping_email' => config('services.shiprocket.email'),
                'shipping_phone' => config('services.shiprocket.warehouse_phone', ''),
                'shipping_is_billing' => false,
                
                'order_items' => $returnItems,
                'payment_method' => 'Prepaid', // No COD for returns
                'sub_total' => $order->subtotal,
                'length' => 10,
                'breadth' => 10,
                'height' => 10,
                'weight' => $totalWeight,
            ];

            \Log::info('📦 Creating Shiprocket return pickup', [
                'return_id' => $return->id,
                'order_number' => $order->order_number,
                'total_weight_kg' => $totalWeight,
                'pickup_from' => $order->city . ' - ' . $order->zip,
                'deliver_to' => config('services.shiprocket.warehouse_city') . ' - ' . config('services.shiprocket.pickup_pincode')
            ]);

            // Create return order in Shiprocket
            $result = $shiprocketService->createReturnPickup($returnOrderData);

            if ($result && isset($result['order_id'])) {
                // Update return record with Shiprocket details
                $return->update([
                    'shiprocket_return_order_id' => $result['order_id'],
                    'shiprocket_return_shipment_id' => $result['shipment_id'] ?? null,
                    'return_pickup_scheduled_at' => now(),
                ]);

                \Log::info('✅ Shiprocket return pickup created successfully', [
                    'return_id' => $return->id,
                    'shiprocket_return_order_id' => $result['order_id'],
                    'shiprocket_return_shipment_id' => $result['shipment_id'] ?? null
                ]);
            } else {
                \Log::error('❌ Failed to create Shiprocket return pickup', [
                    'return_id' => $return->id,
                    'result' => $result
                ]);
            }

        } catch (\Exception $e) {
            // Log error but don't fail the return approval
            \Log::error('❌ Exception creating Shiprocket return pickup', [
                'return_id' => $return->id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    /**
     * Increase stock for returned items
     */
    private function increaseStock($order)
    {
        try {
            foreach ($order->items as $item) {
                if (isset($item['product_id']) && isset($item['size']) && isset($item['color'])) {
                    // Find the color ID from color name
                    $color = ProductColor::where('color', $item['color'])->first();
                    
                    if ($color) {
                        // Find the variant
                        $variant = ProductVariant::where('product_id', $item['product_id'])
                            ->where('color_id', $color->id)
                            ->where('size', $item['size'])
                            ->first();
                        
                        if ($variant) {
                            // Increase stock
                            $newStock = $variant->stock + $item['quantity'];
                            $variant->update(['stock' => $newStock]);
                            
                            \Log::info('Stock increased (return approved)', [
                                'product_id' => $item['product_id'],
                                'variant_id' => $variant->id,
                                'color' => $item['color'],
                                'size' => $item['size'],
                                'quantity_increased' => $item['quantity'],
                                'old_stock' => $variant->stock - $item['quantity'],
                                'new_stock' => $newStock,
                                'order_id' => $order->id
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the return approval
            \Log::error('Failed to increase stock: ' . $e->getMessage(), [
                'order_id' => $order->id
            ]);
        }
    }
}
