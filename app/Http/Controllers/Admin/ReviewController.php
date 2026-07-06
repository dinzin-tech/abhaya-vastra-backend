<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Products;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Display Review listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.review.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch Review rows for AJAX listing.
     */
    public function listReview(Request $request)
    {
        $query = Review::with('product');
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('review', 'like', "%{$request->q}%")
                  ->orWhereHas('product', function($q2) use ($request) {
                      $q2->where('name', 'like', "%{$request->q}%");
                  });
            });
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.review.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show form for creating or editing a review.
     */
    public function create()
    {
        $products = Products::all();
        return view('admin.modules.review.add', [
            'item' => false,
            'products' => $products
        ]);
    }

    /**
     * Store or update a review.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'product_id' => 'nullable|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'review'     => 'required|string|max:500',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $request->only(['name', 'product_id', 'rating', 'review']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('reviews', 'public');
        }

        if ($request->id) {
            $review = Review::findOrFail($request->id);

            // Delete old image if replaced or explicitly requested to be removed
            if (($request->remove_image == '1' || $request->hasFile('image')) && $review->image) {
                Storage::disk('public')->delete($review->image);
                if ($request->remove_image == '1' && !$request->hasFile('image')) {
                    $data['image'] = null;
                }
            }

            $review->update($data);
            $message = 'Review Updated Successfully';
        } else {
            Review::create($data);
            $message = 'Review Added Successfully';
        }

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => route('reviews.index')
        ]);
    }

    /**
     * Edit a review.
     */
    public function edit($id)
    {
        $item = Review::findOrFail($id);
        $products = Products::all();

        return view('admin.modules.review.add', [
            'item' => $item,
            'products' => $products
        ]);
    }

    /**
     * Delete a review.
     */
    public function delete(Request $request)
    {
        $review = Review::findOrFail($request->id);

        // Delete stored image
        if ($review->image) {
            Storage::disk('public')->delete($review->image);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully!',
        ], 200);
    }
}
