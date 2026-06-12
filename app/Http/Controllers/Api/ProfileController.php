<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class ProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip,
                'shipping_address' => $user->shipping_address,
                'shipping_city' => $user->shipping_city,
                'shipping_state' => $user->shipping_state,
                'shipping_zip' => $user->shipping_zip,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_zip' => 'nullable|string|max:20',
        ]);

        // Save personal info
        $user->fill($request->only(['name', 'email', 'address', 'city', 'state', 'zip']));

        // Save shipping info (handle "same as billing")
        if ($request->same_as_billing) {
            $user->shipping_address = $request->address;
            $user->shipping_city = $request->city;
            $user->shipping_state = $request->state;
            $user->shipping_zip = $request->zip;
        } else {
            $user->fill($request->only(['shipping_address', 'shipping_city', 'shipping_state', 'shipping_zip']));
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'Profile updated successfully!']);
    }
}
