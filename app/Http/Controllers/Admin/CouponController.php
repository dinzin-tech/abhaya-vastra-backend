<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\User;
use App\Models\CouponUser;
use Illuminate\Http\Request;
use App\Mail\CouponMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;


class CouponController extends Controller
{
    /**
     * Display the listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.coupons.list', [
            'q' => $request->q,
            'offset' => $request->offset ?? 10,
        ]);
    }

    /**
     * Fetch coupon rows for AJAX listing.
     */
    public function listCoupons(Request $request)
    {
        $query = Coupon::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('code', 'like', "%{$request->q}%");
        }

 
        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'success' => true,
            'rows' => view('admin.modules.coupons.list_rows', ['coupons' => $items])->render(),
            'items' => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show form for creating new coupon.
     */
    public function create()
    {
        return view('admin.modules.coupons.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update coupon.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => $request->id ? 'required|string|max:50' : 'required|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric',
            'min_cart_amount' => 'required|numeric',
            'usage_limit' => 'nullable|integer',
            'expires_at' => 'nullable|date',
        ]);

        $data = $request->only([
            'code',
            'type',
            'value',
            'min_cart_amount',
            'usage_limit',
            'expires_at'
        ]);

        $data['status'] = $request->has('status') ? 1 : 0;

        if ($request->id) {
            $coupon = Coupon::findOrFail($request->id);
            $coupon->update($data);
            $message = 'Coupon Updated';
        } else {
            Coupon::create($data);
            $message = 'Coupon Added';
        }

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => route('coupons.index')
        ]);
    }

    /**
     * Show form for editing coupon.
     */
    public function edit($id)
    {
        $item = Coupon::findOrFail($id);
        return view('admin.modules.coupons.add', [
            'item' => $item
        ]);
    }






    public function delete(Request $request)
    {
        $coupon = Coupon::findOrFail($request->id);
        
        // Delete related coupon_user entries first
        $coupon->users()->detach();
        
        // Then delete the coupon
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted successfully',
        ]);
    }
    
    /**
     * Assign coupon to users.
     */
    public function assignToUsers(Request $request)
    {
        $request->validate([
            'coupon_id' => 'required|exists:coupons,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'message_body' => 'nullable|string',
        ]);

        $coupon = Coupon::findOrFail($request->coupon_id);
        $userIds = $request->user_ids;
        
        // Check if users are already assigned this coupon
        $existingAssignments = $coupon->users()->whereIn('user_id', $userIds)->pluck('user_id')->toArray();
        $newUserIds = array_diff($userIds, $existingAssignments);
        
        if (empty($newUserIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Selected users already have this coupon assigned.'
            ], 422);
        }
        
        // Prepare data for bulk insert
        $now = now();
        $assignments = [];
        $emails = [];
        
        foreach ($newUserIds as $userId) {
            $assignments[] = [
                'coupon_id' => $coupon->id,
                'user_id' => $userId,
                'used' => false,
                'created_at' => $now,
                'updated_at' => $now
            ];
            
            // Get user email for sending notification
            $user = User::find($userId);
            if ($user && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $user->email;
            }
        }
        
        // Insert assignments in a transaction
        DB::beginTransaction();
        
        try {
            // Insert the coupon assignments
            DB::table('coupon_user')->insert($assignments);
            
            // Send email notifications
            foreach (array_unique($emails) as $email) {
                try {
                    Mail::to($email)->queue(new CouponMail($coupon, $request->message_body ?? ''));
                } catch (\Exception $e) {
                    // Log email sending errors but don't fail the whole operation
                    \Log::error('Failed to send coupon email to ' . $email . ': ' . $e->getMessage());
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Coupon assigned to ' . count($newUserIds) . ' users successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to assign coupons: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign coupon. Please try again.'
            ], 500);
        }
    }


}
