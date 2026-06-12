<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;

class GalleryController extends Controller
{
    /**
     * Display the Gallery listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.gallery.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch Gallery rows for AJAX listing.
     */
    public function listGallery(Request $request)
    {
        $query = Gallery::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            // Optional: filter by image name
            $query->where('image', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows' => view('admin.modules.gallery.list_rows', ['items' => $items])->render(),
            'items' => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating Gallery.
     */
    public function create()
    {
        return view('admin.modules.gallery.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update a Gallery entry.
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
            $path = $request->file('image')->store('gallerys', 'public');
            $data['image'] = $path;
        }

        if ($request->id) {
            $gallery = Gallery::findOrFail($request->id);
            $gallery->update($data);
            $message = 'Gallery Updated';
        } else {
            Gallery::create($data);
            $message = 'Gallery Added';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect' => route('gallery.index')
        ]);
    }

    /**
     * Show the form for editing Gallery.
     */
    public function edit($id)
    {
        $item = Gallery::findOrFail($id);
        return view('admin.modules.gallery.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete Gallery entry.
     */
    public function delete(Request $request)
    {
        $gallery = Gallery::findOrFail($request->id);
        $gallery->delete();

        return response()->json([
            'message' => 'Deleted Successfully!',
        ], 200);
    }
}
