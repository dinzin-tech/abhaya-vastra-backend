<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Services\RewardService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    /**
     * Display the Reward Transactions listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.rewards.list', [
            'q'      => $request->q,
            'status' => $request->status,
            'offset' => $request->offset,
        ]);
    }

    /**
     * Fetch Reward Transactions rows for AJAX listing.
     */
    public function listTransactions(Request $request)
    {
        $query = WalletTransaction::with(['wallet.user'])
            ->orderBy('id', 'desc');

        // Search filter
        if ($request->q) {
            $query->where('description', 'like', "%{$request->q}%")
                ->orWhere('reference', 'like', "%{$request->q}%")
                ->orWhereHas('wallet.user', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->q}%")
                        ->orWhere('email', 'like', "%{$request->q}%");
                });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $offset = $request->offset ?? 10;
        $items = $query->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.rewards.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Approve a pending transaction (credit the user's wallet).
     */
    public function approveTransaction(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:wallet_transactions,id',
        ]);

        $transaction = WalletTransaction::with('wallet.user')->findOrFail($request->id);

        if ($transaction->status !== 'pending' || $transaction->type !== 'credit') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending credit transactions can be approved.',
            ], 422);
        }

        $service = new RewardService($transaction->wallet->user_id);
        $service->approveTransaction($transaction);

        return response()->json([
            'success' => true,
            'message' => 'Reward approved and credited successfully!',
        ]);
    }

    /**
     * Reverse a pending transaction (e.g. cancel).
     */
    public function reverseTransaction(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:wallet_transactions,id',
        ]);

        $transaction = WalletTransaction::findOrFail($request->id);

        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending transactions can be reversed.',
            ], 422);
        }

        $service = new RewardService($transaction->wallet->user_id);
        $service->reverseTransaction($transaction);

        return response()->json([
            'success' => true,
            'message' => 'Transaction reversed successfully.',
        ]);
    }

    /**
     * Show detailed view of a reward transaction.
     */
    public function show($id)
    {
        $item = WalletTransaction::with('wallet.user')->findOrFail($id);
        return view('admin.modules.rewards.view', ['item' => $item]);
    }

    /**
     * Delete a transaction (optional for admin cleanup).
     */
    public function delete(Request $request)
    {
        $request->validate(['id' => 'required|exists:wallet_transactions,id']);
        $transaction = WalletTransaction::findOrFail($request->id);
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully!',
        ]);
    }
}
