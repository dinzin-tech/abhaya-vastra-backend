<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomDesignController extends Controller
{
    /**
     * Upload custom print design layout image.
     */
    public function uploadDesign(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:8192', // Max 8MB for high-res print designs
        ]);

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'design_' . Str::random(20) . '.' . $file->getClientOriginalExtension();
                
                // Store in public/custom_designs folder
                $path = $file->storeAs('custom_designs', $filename, 'public');
                $url = asset('storage/' . $path);

                return response()->json([
                    'success' => true,
                    'message' => 'Custom design uploaded successfully.',
                    'url' => $url,
                    'path' => $path
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'No design image found in the request.'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save custom design: ' . $e->getMessage()
            ], 500);
        }
    }
}
