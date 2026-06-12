<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Privacy;

class PrivacyController extends Controller
{
    /**
     * Display the About Us page listing.
     */
    public function index(Request $request)
    {
        return view('admin.modules.privacy.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch About Us rows for AJAX listing.
     */
    public function listPrivacy(Request $request)
    {
        $query = Privacy::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows' => view('admin.modules.privacy.list_rows', ['items' => $items])->render(),
            'items' => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating About Us.
     */
    public function create()
    {
        return view('admin.modules.privacy.add', [
            'item' => false
        ]);
    }

    /**
     * Store a newly created About Us entry.
     */
   public function store(Request $request)
{

    $request->validate([
        'title' => 'required',
        'description' => 'required'
    ]);

    if ($request->id) {
        $privacy = Privacy::findOrFail($request->id);
        $privacy->update([
            'title' => $request->title,
            'description' => $request->description
        ]);
        $message = 'About Us Updated';
    } else {
        Privacy::create([
            'title' => $request->title,
            'description' => $request->description
        ]);
        $message = 'About Us Added';
    }

    return response()->json([
        'success'  => true,
        'message'  => $message,
        'redirect' => route('privacy.index') 
    ]);
}


    /**
     * Show the form for editing About Us.
     */
    public function edit($id)
    {
        $item = Privacy::findOrFail($id);
        return view('admin.modules.privacy.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete About Us entry.
     */
    public function delete(Request $request)
    {
        $privacy = Privacy::findOrFail($request->id);
        $privacy->delete();

        return response()->json([
            'message' => 'Deleted Successfully!',
        ], 200);
    }

    /**
     * Optional: Change status (active/inactive) if needed.
     */
   
}
