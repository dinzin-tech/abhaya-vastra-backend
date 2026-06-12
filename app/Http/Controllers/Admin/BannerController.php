<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;

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
        $request->validate([
        'image' => $request->id
    ? 'nullable|file|mimes:jpeg,png,jpg,gif,webp,avif,heic,heif|max:2048'
    : 'required|file|mimes:jpeg,png,jpg,gif,webp,avif,heic,heif|max:2048',

        ]);

        $data = [];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $data['image'] = $path;
        }

        if ($request->id) {
            $banner = Banner::findOrFail($request->id);
            $banner->update($data);
            $message = 'Banner Updated';
        } else {
            Banner::create($data);
            $message = 'Banner Added';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect' => route('banner.index')
        ]);
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
