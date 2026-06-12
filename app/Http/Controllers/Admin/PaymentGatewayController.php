<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;

class PaymentGatewayController extends Controller
{
    /**
     * Display Payment Gateway listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.payment-gateway.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch Payment Gateway rows for AJAX listing.
     */
    public function listGateways(Request $request)
    {
        $query = PaymentGateway::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('gateway_name', 'like', "%{$request->q}%")
                  ->orWhere('api_key', 'like', "%{$request->q}%")
                  ->orWhere('api_secret', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.payment-gateway.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show form for creating new Payment Gateway.
     */
    public function create()
    {
        return view('admin.modules.payment-gateway.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update Payment Gateway.
     */
    public function store(Request $request)
    {
        $request->validate([
            'gateway_name' => 'required|string|max:50',
            'currency'     => 'required|string|max:10',
            'api_key'      => 'required|string|max:255',
            'api_secret'   => 'required|string|max:255',
        ]);

        if ($request->id) {
            $gateway = PaymentGateway::findOrFail($request->id);
            $gateway->update($request->only(['gateway_name','currency','api_key','api_secret']));
            $message = 'Payment Gateway Updated';
        } else {
            PaymentGateway::create($request->only(['gateway_name','currency','api_key','api_secret']));
            $message = 'Payment Gateway Added';
        }

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => route('payment-gateway.index')
        ]);
    }

    /**
     * Show form for editing Payment Gateway.
     */
    public function edit($id)
    {
        $item = PaymentGateway::findOrFail($id);
        return view('admin.modules.payment-gateway.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete Payment Gateway.
     */
    public function delete(Request $request)
    {
        $gateway = PaymentGateway::findOrFail($request->id);
        $gateway->delete();

        return response()->json([
            'message' => 'Deleted Successfully!',
        ], 200);
    }
}
