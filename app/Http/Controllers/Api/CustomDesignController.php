<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CustomDesignController extends Controller
{
    /**
     * Upload custom print design layout image.
     */
    public function uploadDesign(Request $request)
    {
        $request->validate([
            'image'  => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:8192', // Max 8MB for high-res print designs
            'mockup' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096', // Max 4MB for mockup previews
        ]);

        try {
            $responseData = [
                'success' => true,
                'message' => 'Uploads completed successfully.'
            ];

            // 1. Process printable design layout
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'design_' . Str::random(20) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('custom_designs', $filename, 'public');
                $responseData['url'] = asset('storage/' . $path);
                $responseData['path'] = $path;
            }

            // 2. Process visual mockup preview
            if ($request->hasFile('mockup')) {
                $mockupFile = $request->file('mockup');
                $mockupFilename = 'mockup_' . Str::random(20) . '.' . $mockupFile->getClientOriginalExtension();
                $mockupPath = $mockupFile->storeAs('custom_designs', $mockupFilename, 'public');
                $responseData['mockup_url'] = asset('storage/' . $mockupPath);
                $responseData['mockup_path'] = $mockupPath;
            }

            return response()->json($responseData, 201);

        } catch (\Exception $e) {
            Log::error('Failed custom design uploads: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save custom upload: ' . $e->getMessage()
            ], 500);
        }
    }
}
