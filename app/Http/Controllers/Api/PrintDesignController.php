<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrintDesign;
use App\Services\StorageService;
use Illuminate\Http\Request;

class PrintDesignController extends Controller
{
    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index(Request $request)
    {
        $query = PrintDesign::with('category')->where('status', 'active');

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $designs = $query->latest()->paginate($request->get('limit', 24));

        return response()->json([
            'success' => true,
            'designs' => $designs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:design_categories,id',
            'title' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'images.*' => 'image|max:10240'
        ]);

        $categoryId = $request->category_id;
        $uploadedDesigns = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storageService->upload($file, 'designs');
                $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                $design = PrintDesign::create([
                    'category_id' => $categoryId,
                    'title' => $title,
                    'image_path' => $path,
                    'status' => 'active'
                ]);
                $uploadedDesigns[] = $design;
            }
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $this->storageService->upload($file, 'designs');
            $title = $request->get('title', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

            $design = PrintDesign::create([
                'category_id' => $categoryId,
                'title' => $title,
                'image_path' => $path,
                'status' => 'active'
            ]);
            $uploadedDesigns[] = $design;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No image file uploaded.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedDesigns) . ' design(s) uploaded successfully.',
            'designs' => $uploadedDesigns
        ]);
    }

    public function destroy($id)
    {
        $design = PrintDesign::findOrFail($id);
        $this->storageService->delete($design->image_path);
        $design->delete();

        return response()->json([
            'success' => true,
            'message' => 'Design deleted successfully.'
        ]);
    }
}
