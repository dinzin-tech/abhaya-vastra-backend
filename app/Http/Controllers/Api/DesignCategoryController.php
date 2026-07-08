<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DesignCategoryController extends Controller
{
    public function index()
    {
        $categories = DesignCategory::withCount('designs')->get();
        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:design_categories,name',
        ]);

        $category = DesignCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'category' => $category
        ]);
    }

    public function destroy($id)
    {
        $category = DesignCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ]);
    }
}
