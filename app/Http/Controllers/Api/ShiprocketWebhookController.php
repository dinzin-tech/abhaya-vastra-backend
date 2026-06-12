<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderExchange;
use App\Mail\OrderShippedMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\ExchangeStatusMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ShiprocketWebhookController extends Controller
{
    /**
     * Handle Shiprocket webhook for order status updates
     */
    public function handleWebhook(Request $request)
    {
        try {
            Log::info('Shiprocket webhook received', $request->all());

            $data = $request->all();

            // Shiprocket sends order_id (channel_order_id) in webhook
            $orderNumber = $data['order_id'] ?? $data['channel_order_id'] ?? null;
            
            if (!$orderNumber) {
                Log::warning('Shiprocket webhook: No order ID found');
                return response()->json(['message' => 'No order ID'], 400);
            }

            // Find order by order_number (normal e-commerce order flow)
            $order = Order::where('order_number', $orderNumber)->first();

            // Shipment details
            $shipmentStatus = strtolower($data['shipment_status'] ?? $data['status'] ?? '');
            $awbCode = $data['awb_code'] ?? $data['awb'] ?? null;
            $courierName = $data['courier_name'] ?? $data['courier'] ?? null;
            $trackingUrl = $data['tracking_url'] ?? null;

            // If normal order not found, try Exchange flows
            if (!$order) {
                Log::info('Shiprocket webhook: normal order not found, attempting exchange match', ['order_identifier' => $orderNumber]);

                // Try direct association using stored Shiprocket order IDs on exchanges
                $exchange = OrderExchange::where('shiprocket_pickup_order_id', $orderNumber)
                    ->orWhere('shiprocket_delivery_order_id', $orderNumber)
                    ->first();

                // Fallback: parse our custom channel order id formats: EXC-PICKUP-<id>-<timestamp> or EXC-DELIVERY-<id>-<timestamp>
                if (!$exchange && (stripos($orderNumber, 'EXC-PICKUP-') === 0 || stripos($orderNumber, 'EXC-DELIVERY-') === 0)) {
                    $parts = explode('-', $orderNumber);
                    // Expected: [EXC, PICKUP|DELIVERY, <id>, <timestamp>]
                    if (count($parts) >= 3 && is_numeric($parts[2])) {
                        $exchangeId = (int) $parts[2];
                        $exchange = OrderExchange::find($exchangeId);
                    }
                }

                if ($exchange) {
                    $isPickup = $exchange->shiprocket_pickup_order_id === $orderNumber || stripos($orderNumber, 'EXC-PICKUP-') === 0;
                    $isDelivery = $exchange->shiprocket_delivery_order_id === $orderNumber || stripos($orderNumber, 'EXC-DELIVERY-') === 0;

                    Log::info('Shiprocket webhook matched to exchange', [
                        'exchange_id' => $exchange->id,
                        'is_pickup' => $isPickup,
                        'is_delivery' => $isDelivery,
                        'shipment_status' => $shipmentStatus,
                    ]);

                    // Update tracking fields on exchange
                    if ($isPickup) {
                        if ($awbCode) { $exchange->shiprocket_pickup_awb_code = $awbCode; }
                        if ($courierName) { $exchange->shiprocket_pickup_courier_name = $courierName; }
                    }
                    if ($isDelivery) {
                        if ($awbCode) { $exchange->shiprocket_delivery_awb_code = $awbCode; }
                        if ($courierName) { $exchange->shiprocket_delivery_courier_name = $courierName; }
                    }

                    // Map status for exchange
                    if ($isPickup) {
                        $newExchangeStatus = $this->mapShiprocketPickupToExchangeStatus($shipmentStatus);
                    } else {
                        $newExchangeStatus = $this->mapShiprocketDeliveryToExchangeStatus($shipmentStatus);
                    }

                    if ($newExchangeStatus && $exchange->status !== $newExchangeStatus) {
                        $oldExStatus = $exchange->status;
                        $exchange->status = $newExchangeStatus;

                        // If delivery completed, set delivered_at
                        if ($newExchangeStatus === 'completed' && empty($exchange->delivered_at)) {
                            $exchange->delivered_at = now();
                        }

                        $exchange->save();

                        Log::info('Exchange status updated via Shiprocket webhook', [
                            'exchange_id' => $exchange->id,
                            'old_status' => $oldExStatus,
                            'new_status' => $newExchangeStatus,
                        ]);

                        // Send exchange status email to customer
                        try {
                            if ($exchange->relationLoaded('order')) {
                                $order = $exchange->order;
                            } else {
                                $order = $exchange->order; // lazy load
                            }
                            if ($order && !empty($order->email)) {
                                Mail::to($order->email)->queue(new ExchangeStatusMail($exchange->load('order')));
                                Log::info('Exchange status email queued', [
                                    'exchange_id' => $exchange->id,
                                    'status' => $newExchangeStatus,
                                    'email' => $order->email,
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to send exchange status email: ' . $e->getMessage());
                        }
                    } else {
                        // Save any updated tracking details
                        $exchange->save();
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Exchange webhook processed successfully'
                    ]);
                }

                Log::warning('Shiprocket webhook: No matching order or exchange found', ['identifier' => $orderNumber]);
                return response()->json(['message' => 'No matching order or exchange'], 404);
            }

            Log::info('Processing Shiprocket status update', [
                'order_number' => $orderNumber,
                'shipment_status' => $shipmentStatus,
                'awb_code' => $awbCode
            ]);

            // Update Shiprocket tracking details
            if ($awbCode) {
                $order->shiprocket_awb_code = $awbCode;
            }
            if ($courierName) {
                $order->shiprocket_courier_name = $courierName;
            }
            if ($trackingUrl) {
                $order->shiprocket_tracking_url = $trackingUrl;
            }

            // Map Shiprocket status to our order status
            $newStatus = $this->mapShiprocketStatus($shipmentStatus);
            
            if ($newStatus && $order->status !== $newStatus) {
                $oldStatus = $order->status;
                $order->status = $newStatus;
                
                // Set delivered_at timestamp when delivered
                if ($newStatus === 'delivered' && !$order->delivered_at) {
                    $order->delivered_at = now();
                }
                
                $order->save();

                Log::info('Order status updated via Shiprocket webhook', [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);

                // Send email notifications
                $this->sendStatusEmail($order, $newStatus);
            } else {
                $order->save(); // Save tracking details even if status unchanged
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Shiprocket webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Map Shiprocket status to our order status
     */
    private function mapShiprocketStatus($shiprocketStatus)
    {
        $statusMap = [
            'pickup scheduled' => 'processing',
            'pickup queued' => 'processing',
            'pickup generated' => 'processing',
            'manifested' => 'processing',
            'dispatched' => 'shipped',
            'shipped' => 'shipped',
            'in transit' => 'shipped',
            'out for delivery' => 'shipped',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
            'rto initiated' => 'cancelled',
            'rto delivered' => 'cancelled',
        ];

        return $statusMap[$shiprocketStatus] ?? null;
    }

    /**
     * Map Shiprocket pickup (return) statuses to Exchange status values
     */
    private function mapShiprocketPickupToExchangeStatus($shiprocketStatus)
    {
        $status = strtolower($shiprocketStatus);
        $map = [
            'pickup scheduled' => 'pickup_scheduled',
            'pickup queued' => 'pickup_scheduled',
            'pickup generated' => 'pickup_scheduled',
            'manifested' => 'pickup_scheduled',
            // Once the courier has the item and it's moving
            'dispatched' => 'pickup_scheduled',
            'shipped' => 'pickup_scheduled',
            'in transit' => 'pickup_scheduled',
            'out for delivery' => 'pickup_scheduled',
            // When returned parcel delivered back to seller, consider original picked up
            'delivered' => 'picked_up',
            'cancelled' => 'cancelled',
            'rto initiated' => 'cancelled',
            'rto delivered' => 'cancelled',
        ];
        return $map[$status] ?? null;
    }

    /**
     * Map Shiprocket forward (delivery) statuses to Exchange status values
     */
    private function mapShiprocketDeliveryToExchangeStatus($shiprocketStatus)
    {
        $status = strtolower($shiprocketStatus);
        $map = [
            'pickup scheduled' => 'exchange_shipped',
            'pickup queued' => 'exchange_shipped',
            'pickup generated' => 'exchange_shipped',
            'manifested' => 'exchange_shipped',
            'dispatched' => 'exchange_shipped',
            'shipped' => 'exchange_shipped',
            'in transit' => 'exchange_shipped',
            'out for delivery' => 'exchange_shipped',
            'delivered' => 'completed',
            'cancelled' => 'cancelled',
            'rto initiated' => 'cancelled',
            'rto delivered' => 'cancelled',
        ];
        return $map[$status] ?? null;
    }

    /**
     * Send email notification based on status
     */
    private function sendStatusEmail($order, $status)
    {
        try {
            if ($status === 'shipped') {
                Mail::to($order->email)->queue(new OrderShippedMail($order));
                Log::info('Shipped email queued', ['order_id' => $order->id]);
            } elseif ($status === 'delivered') {
                Mail::to($order->email)->queue(new OrderDeliveredMail($order));
                Log::info('Delivered email queued', ['order_id' => $order->id]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send status email: ' . $e->getMessage());
        }
    }

    /**
     * Manual sync - fetch tracking from Shiprocket API
     */
    public function syncTracking($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            if (!$order->shiprocket_shipment_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Shiprocket shipment ID found'
                ], 400);
            }

            $shiprocketService = app(\App\Services\ShiprocketService::class);
            $tracking = $shiprocketService->trackShipment($order->shiprocket_shipment_id);

            if ($tracking && isset($tracking['tracking_data'])) {
                $trackingData = $tracking['tracking_data'];
                $currentStatus = strtolower($trackingData['shipment_status'] ?? '');
                
                $newStatus = $this->mapShiprocketStatus($currentStatus);
                
                if ($newStatus && $order->status !== $newStatus) {
                    $order->status = $newStatus;
                    
                    if ($newStatus === 'delivered' && !$order->delivered_at) {
                        $order->delivered_at = now();
                    }
                    
                    $order->save();
                    
                    $this->sendStatusEmail($order, $newStatus);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Tracking synced successfully',
                    'data' => $tracking
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch tracking data'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Tracking sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error syncing tracking: ' . $e->getMessage()
            ], 500);
        }
    }
}
