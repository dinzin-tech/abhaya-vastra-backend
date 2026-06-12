<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocialLink;

class SocialLinkController extends Controller
{
    /**
     * Display listing page
     */
    public function index(Request $request)
    {
        return view('admin.modules.social-icons.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch rows for AJAX listing
     */
    public function listSocial(Request $request)
    {
        $query = SocialLink::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('title', 'like', "%{$request->q}%")
                  ->orWhere('icon', 'like', "%{$request->q}%")
                  ->orWhere('url', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.social-icons.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show form for creating new Social Icon
     */
    public function create()
    {
        return view('admin.modules.social-icons.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'icon'  => 'required|string|max:100', // fa fa-facebook
            'url'   => 'required|url|max:255',
        ]);

        if ($request->id) {
            $social = SocialLink::findOrFail($request->id);
            $social->update($request->only(['title', 'icon', 'url']));
            $message = 'Social Link Updated';
        } else {
            SocialLink::create($request->only(['title', 'icon', 'url']));
            $message = 'Social Link Added';
        }

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => route('social.index')
        ]);
    }

    /**
     * Show form for editing
     */
    public function edit($id)
    {
        $item = SocialLink::findOrFail($id);
        return view('admin.modules.social-icons.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete
     */
    public function delete(Request $request)
    {
        $social = SocialLink::findOrFail($request->id);
        $social->delete();

        return response()->json([
            'message' => 'Deleted Successfully!',
        ], 200);
    }
}
