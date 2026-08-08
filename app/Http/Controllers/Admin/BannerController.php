<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;

use App\Helpers\ImageHelper;

class BannerController extends Controller
{
    /**
     * Display the Banner listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.banner.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch Banner rows for AJAX listing.
     */
    public function listBanners(Request $request)
    {
        $query = Banner::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            // Optional: filter by image name
            $query->where('image', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows' => view('admin.modules.banner.list_rows', ['items' => $items])->render(),
            'items' => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating Banner.
     */
    public function create()
    {
        return view('admin.modules.banner.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update a Banner entry.
     */
    public function store(Request $request)
    {
        try {
            $isUpdate = !empty($request->id);

            $request->validate([
                'image' => ($isUpdate || $request->hasFile('mobile_image'))
                    ? 'nullable|file|max:10240'
                    : 'required|file|max:10240',
                'mobile_image' => 'nullable|file|max:10240',
            ]);

            $data = [];

            if ($request->hasFile('image')) {
                $path = ImageHelper::convertAndStoreToWebp($request->file('image'), 'banners');
                $data['image'] = $path;
            }

            if ($request->hasFile('mobile_image')) {
                $mobilePath = ImageHelper::convertAndStoreToWebp($request->file('mobile_image'), 'banners');
                $data['mobile_image'] = $mobilePath;
            }

            if (empty($data['image']) && !empty($data['mobile_image'])) {
                $data['image'] = $data['mobile_image'];
            }

            if (empty($data['mobile_image']) && !empty($data['image'])) {
                $data['mobile_image'] = $data['image'];
            }

            if ($isUpdate) {
                $banner = Banner::findOrFail($request->id);
                if (!empty($data)) {
                    $banner->update($data);
                }
                $message = 'Banner Updated Successfully';
            } else {
                Banner::create($data);
                $message = 'Banner Added Successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('banner.index')
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation error.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Banner store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save banner: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing Banner.
     */
    public function edit($id)
    {
        $item = Banner::findOrFail($id);
        return view('admin.modules.banner.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete Banner entry.
     */
    public function delete(Request $request)
    {
        $banner = Banner::findOrFail($request->id);
        $banner->delete();

        return response()->json([
            'message' => 'Deleted Successfully!',
        ], 200);
    }
}
