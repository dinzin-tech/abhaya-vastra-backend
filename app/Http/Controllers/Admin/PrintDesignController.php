<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintDesign;
use App\Models\DesignCategory;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrintDesignController extends Controller
{
    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index(Request $request)
    {
        $categories = DesignCategory::all();
        $query = PrintDesign::with('category');

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $designs = $query->latest()->paginate(16);

        return view('admin.modules.print_designs.index', compact('designs', 'categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:design_categories,name',
        ]);

        DesignCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->back()->with('success', 'Design Category created successfully.');
    }

    public function destroyCategory($id)
    {
        $category = DesignCategory::findOrFail($id);
        $category->delete();

        return redirect()->back()->with('success', 'Design Category deleted successfully.');
    }

    public function storeDesign(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:design_categories,id',
            'images' => 'required|array',
            'images.*' => 'image|max:10240'
        ]);

        $categoryId = $request->category_id;
        $count = 0;

        foreach ($request->file('images') as $file) {
            $path = $this->storageService->upload($file, 'designs');
            $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            PrintDesign::create([
                'category_id' => $categoryId,
                'title' => $title,
                'image_path' => $path,
                'status' => 'active'
            ]);
            $count++;
        }

        return redirect()->back()->with('success', $count . ' design(s) uploaded successfully.');
    }

    public function destroyDesign($id)
    {
        $design = PrintDesign::findOrFail($id);
        $this->storageService->delete($design->image_path);
        $design->delete();

        return redirect()->back()->with('success', 'Design deleted successfully.');
    }
}
