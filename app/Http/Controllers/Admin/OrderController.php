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

    /**
     * Update order status.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($request->order_id);
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
}
