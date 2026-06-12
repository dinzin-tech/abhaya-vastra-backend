<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReturnRequestedMail;
use App\Mail\AdminReturnNotificationMail;


class ReturnController extends Controller
{
    /**
     * Get all return requests for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $returns = OrderReturn::where('user_id', $user->id)
                ->with(['order'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Return requests fetched successfully',
                'data' => $returns
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching return requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new return request
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'reason' => 'required|string',
                'images' => 'nullable|array',
                'images.*' => 'string', // Base64 encoded images
                'tracking_id' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the order
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Check if order can be returned
            if (!$order->canBeReturned()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order cannot be returned. Returns are only allowed within 7 days of delivery.'
                ], 422);
            }

            // Check if order already has a return request
            if ($order->hasReturnRequest()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A return request already exists for this order.'
                ], 422);
            }

            // Process and save images
            $savedImageUrls = [];
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $base64Image) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                        $imageType = $matches[1];
                        $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                        $imageData = base64_decode($imageData);

                        if ($imageData !== false) {
                            $filename = 'return_' . $order->id . '_' . time() . '_' . uniqid() . '.' . $imageType;
                            $path = 'returns/' . $filename;

                            // ✅ Save image publicly
                            Storage::disk('public')->put($path, $imageData);

                            // ✅ Generate public URL
                            $savedImageUrls[] = asset('storage/' . $path);
                        }
                    }
                }
            }


            // Create return request
            $return = OrderReturn::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'reason' => $request->reason,
                'images' => $savedImageUrls,
                'tracking_id' => $request->tracking_id,
                'status' => 'pending'
            ]);

            // Send email notifications
            try {
                // Email to customer
                Mail::to($order->email)->queue(new ReturnRequestedMail($return->load('order')));
                
                // Email to admin
                $adminEmail = config('mail.from.address') ?? 'admin@example.com';
                Mail::to($adminEmail)->queue(new AdminReturnNotificationMail($return->load('order')));
            } catch (\Exception $e) {
                // Log email error but don't fail the return request
                \Log::error('Failed to send return notification emails: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Return request submitted successfully',
                'data' => $return->load(['order'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating return request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific return request details
     */
    public function show($id)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $return = OrderReturn::where('id', $id)
                ->where('user_id', $user->id)
                ->with(['order'])
                ->first();

            if (!$return) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return request not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Return request details fetched successfully',
                'data' => $return
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching return request: ' . $e->getMessage()
            ], 500);
        }
    }
}
