<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AboutUs;

class AboutUsController extends Controller
{
    /**
     * Display the About Us page listing.
     */
    public function index(Request $request)
    {
        return view('admin.modules.aboutus.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch About Us rows for AJAX listing.
     */
    public function listAboutUs(Request $request)
    {
        $query = AboutUs::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows' => view('admin.modules.aboutus.list_rows', ['items' => $items])->render(),
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
        return view('admin.modules.aboutus.add', [
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
        $aboutUs = AboutUs::findOrFail($request->id);
        $aboutUs->update([
            'title' => $request->title,
            'description' => $request->description
        ]);
        $message = 'About Us Updated';
    } else {
        AboutUs::create([
            'title' => $request->title,
            'description' => $request->description
        ]);
        $message = 'About Us Added';
    }

    return response()->json([
        'success'  => true,
        'message'  => $message,
        'redirect' => route('about-us.index') 
    ]);
}


    /**
     * Show the form for editing About Us.
     */
    public function edit($id)
    {
        $item = AboutUs::findOrFail($id);
        return view('admin.modules.aboutus.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete About Us entry.
     */
    public function delete(Request $request)
    {
        $aboutUs = AboutUs::findOrFail($request->id);
        $aboutUs->delete();

        return response()->json([
            'message' => 'Deleted Successfully!',
        ], 200);
    }

    /**
     * Optional: Change status (active/inactive) if needed.
     */
   
}
