<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lookbook;

class LookbookController extends Controller
{
    /**
     * Display the Lookbook listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.lookbook.list', [
            'q'      => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch Lookbook rows for AJAX listing.
     */
    public function listLookbooks(Request $request)
    {
        $query  = Lookbook::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.lookbook.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating Lookbook.
     */
    public function create()
    {
        return view('admin.modules.lookbook.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update a Lookbook entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link'     => 'nullable|string|max:255',
            'size'     => 'required|in:large,medium,full',
            'image'    => $request->id
                ? 'nullable|file|mimes:jpeg,png,jpg,gif,webp,avif,heic,heif|max:4096'
                : 'required|file|mimes:jpeg,png,jpg,gif,webp,avif,heic,heif|max:4096',
        ]);

        $data = [
            'title'      => $request->title,
            'subtitle'   => $request->subtitle,
            'link'       => $request->link ?? '/all-products',
            'size'       => $request->size,
            'sort_order' => (int) $request->sort_order,
            'is_active'  => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('image')) {
            $path          = $request->file('image')->store('lookbooks', 'public');
            $data['image'] = $path;
        }

        if ($request->id) {
            $item = Lookbook::findOrFail($request->id);
            $item->update($data);
            $message = 'Lookbook Updated';
        } else {
            Lookbook::create($data);
            $message = 'Lookbook Added';
        }

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => route('lookbook.index'),
        ]);
    }

    /**
     * Show the form for editing Lookbook.
     */
    public function edit($id)
    {
        $item = Lookbook::findOrFail($id);
        return view('admin.modules.lookbook.add', ['item' => $item]);
    }

    /**
     * Delete Lookbook entry.
     */
    public function delete(Request $request)
    {
        $item = Lookbook::findOrFail($request->id);
        $item->delete();

        return response()->json(['message' => 'Deleted Successfully!'], 200);
    }
}
