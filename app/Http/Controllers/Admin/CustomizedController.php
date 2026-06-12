<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customized;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class CustomizedController extends Controller
{
    /**
     * List all customize products page
     */
    public function index()
    {
        return view('admin.modules.customized.list');
    }

    /**
     * AJAX: Fetch customize product rows
     */
    public function listCustomized(Request $request)
    {
        $query = Customized::query();

        $items = $query->orderBy('id','desc')->paginate($request->offset ?? 10);

        $data = [
            'rows'       => view('admin.modules.customized.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data,200);
    }

    /**
     * Show form to add customize product
     */
    public function create()
    {
        return view('admin.modules.customized.add', [
            'item' => false
        ]);
    }

    /**
     * Show form to edit customize product
     */
    public function edit($id)
    {
        $item = Customized::findOrFail($id);
        return view('admin.modules.customized.add', [
            'item' => $item
        ]);
    }



public function store(Request $request)
{
    $request->validate([
        'title'       => [
                               'required',
                                'string',
                                'max:255',
                                Rule::unique('customizeds')->ignore($request->id),
                            ],
         'slug'        => [
                               'required',
                                'string',
                                'max:255',
                                Rule::unique('customizeds')->ignore($request->id),
                            ],
        'description' => 'nullable|string',
        'images.*'    => 'mimes:jpeg,png,jpg,gif,webp,avif|max:2048'
    ]);

    $data = $request->only(['title','description']);

    // Generate slug from title
    $slug = \Illuminate\Support\Str::slug($request->title);

    // Ensure slug is unique
    // $count = Customized::where('slug', 'like', "$slug%")->count();
    // if($count > 0){
    //     $slug .= '-' . ($count + 1);
    // }
    $data['slug'] = $slug;

    // Handle new uploaded images
    $imagesArray = [];
    if($request->hasFile('images')){
        foreach($request->file('images') as $img){
            $imagesArray[] = $img->store('customized','public');
        }
    }

    if($request->id){
        // Update case
        $custom = Customized::findOrFail($request->id);

        // Remove old images if requested
        if($request->removed_images){
            foreach(json_decode($request->removed_images) as $img){
                Storage::disk('public')->delete($img);
            }
            $existingImages = $custom->images ? json_decode($custom->images,true) : [];
            $existingImages = array_diff($existingImages, json_decode($request->removed_images,true));
        } else {
            $existingImages = $custom->images ? json_decode($custom->images,true) : [];
        }

        // Merge old + new
        $allImages = array_merge($existingImages, $imagesArray);

        // ❌ If no images left at all
        if(count($allImages) < 1){
            return response()->json(['success'=>false,'message'=>'Please select at least one image'],400);
        }

        $data['images'] = json_encode($allImages);
        $custom->update($data);

        $message = 'Customize Product Updated';
    } else {
        // Create case
        if(count($imagesArray) < 1){
            return response()->json(['success'=>false,'message'=>'Please select at least one image'],400);
        }

        $data['images'] = json_encode($imagesArray);
        Customized::create($data);

        $message = 'Customize Product Added';
    }

    return response()->json([
        'success' => true,
        'message' => $message,
        'redirect'=> route('customized.index')
    ]);
}


    /**
     * Delete customize product
     */
    public function delete(Request $request)
    {
        $custom = Customized::findOrFail($request->id);

        if($custom->images){
            foreach(json_decode($custom->images,true) as $img){
                Storage::disk('public')->delete($img);
            }
        }

        $custom->delete();

        return response()->json(['message'=>'Customize Product Deleted'],200);
    }
}