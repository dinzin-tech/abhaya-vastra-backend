<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;

class PaymentController extends Controller
{
    /**
     * Display the Payments listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.payments.list', [
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch Payments rows for AJAX listing.
     */
    public function listPayments(Request $request)
    {
        $query = Payment::with(['user', 'order']);
        $offset = $request->offset ?? 10;

        // Filter by payment ID
        if ($request->razorpay_payment_id != null && $request->razorpay_payment_id != '') {
            $query->where('razorpay_payment_id', 'like', "%$request->razorpay_payment_id%");
        }

        // Filter by user name
        if ($request->name != null && $request->name != '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->name}%");
            })->orWhereHas('order', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->name}%");
            });
        }
        if ($request->status && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Filter by amount
        if ($request->amount != null && $request->amount != '') {
            $query->where('amount', 'like', "%$request->amount%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.payments.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show payment details.
     */
    public function show($id)
    {
        $payment = Payment::with(['user', 'order'])->findOrFail($id);
        return view('admin.modules.payments.show', ['payment' => $payment]);
    }

    /**
     * Update payment status.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'status' => 'required|in:pending,processing,completed,failed,refunded'
        ]);

        $payment = Payment::findOrFail($request->payment_id);
        $payment->status = $request->status;

        // If payment is completed, mark paid_at
        if ($request->status == 'completed') {
            $payment->paid_at = now();
        }

        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully'
        ]);
    }

    /**
     * Delete a payment.
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:payments,id'
        ]);

        $payment = Payment::findOrFail($request->id);
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully'
        ]);
    }
}
