<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display the Categories listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.categories.list', [
            'q'      => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch Categories rows for AJAX listing.
     */
    public function listCategories(Request $request)
    {
        $query = Category::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('name', 'like', "%{$request->q}%")
                  ->orWhere('gender', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.categories.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new Category.
     */
    public function create()
    {
        return view('admin.modules.categories.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update a Category.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name'   => 'required|string|max:255',
    //         'gender' => 'required|in:male,female,unisex'
    //     ]);

    //     if ($request->id) {
    //         $category = Category::findOrFail($request->id);
    //         $category->update([
    //             'name'   => $request->name,
    //             'gender' => $request->gender
    //         ]);
    //         $message = 'Category Updated';
    //     } else {
    //         Category::create([
    //             'name'   => $request->name,
    //             'gender' => $request->gender
    //         ]);
    //         $message = 'Category Added';
    //     }

    //     return response()->json([
    //         'success'  => true,
    //         'message'  => $message,
    //         'redirect' => route('categories.index')
    //     ]);
    // }
public function store(Request $request)
{
    $request->validate([
        'name'        => 'required|string|max:255',
        // 'gender'      => 'required|in:male,female,unisex',
        // 'main_image'  => $request->id ? 'nullable|image' : 'required|image',
        // 'zoomed_image'=> $request->id ? 'nullable|image' : 'required|image',

        'main_image'  => $request->id ? 'nullable|image' : 'required|file|mimes:jpeg,png,bmp,gif,svg,webp,avif',
        'zoomed_image'=> $request->id ? 'nullable|image' : 'required|file|mimes:jpeg,png,bmp,gif,svg,webp,avif',
    ]);

    $data = [
        'name'   => $request->name,
        // 'gender' => $request->gender,
    ];

    // Handle main image
    if ($request->hasFile('main_image')) {
        if ($request->id && $category = Category::find($request->id)) {
            Storage::disk('public')->delete($category->main_image);
        }
        $data['main_image'] = $request->file('main_image')->store('categories', 'public');
    }

    // Handle zoomed image
    if ($request->hasFile('zoomed_image')) {
        if ($request->id && $category = Category::find($request->id)) {
            Storage::disk('public')->delete($category->zoomed_image);
        }
        $data['zoomed_image'] = $request->file('zoomed_image')->store('categories', 'public');
    }

    if ($request->id) {
        $category = Category::findOrFail($request->id);
        $category->update($data);
        $message = 'Category Updated';
    } else {
        Category::create($data);
        $message = 'Category Added';
    }

    return response()->json([
        'success'  => true,
        'message'  => $message,
        'redirect' => route('categories.index')
    ]);
}

    /**
     * Show the form for editing a Category.
     */
    public function edit($id)
    {
        $item = Category::findOrFail($id);
        return view('admin.modules.categories.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete a Category.
     */
    public function delete(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->delete();

        return response()->json([
            'message' => 'Category Deleted Successfully!',
        ], 200);
    }
}