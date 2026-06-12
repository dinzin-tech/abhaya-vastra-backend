<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    /**
     * Display the FAQ listing page.
     */
    public function index(Request $request)
    {
        return view('admin.modules.faq.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch FAQ rows for AJAX listing.
     */
    public function listFaqs(Request $request)
    {
        $query = Faq::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('question', 'like', "%{$request->q}%")
                  ->orWhere('answer', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows' => view('admin.modules.faq.list_rows', ['items' => $items])->render(),
            'items' => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create()
    {
        return view('admin.modules.faq.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update an FAQ entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required',
            'answer'   => 'required'
        ]);

        if ($request->id) {
            $faq = Faq::findOrFail($request->id);
            $faq->update([
                'question' => $request->question,
                'answer'   => $request->answer
            ]);
            $message = 'FAQ Updated';
        } else {
            Faq::create([
                'question' => $request->question,
                'answer'   => $request->answer
            ]);
            $message = 'FAQ Added';
        }

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => route('faq.index') 
        ]);
    }

    /**
     * Show the form for editing an FAQ.
     */
    public function edit($id)
    {
        $item = Faq::findOrFail($id);
        return view('admin.modules.faq.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete an FAQ entry.
     */
    public function delete(Request $request)
    {
        $faq = Faq::findOrFail($request->id);
        $faq->delete();

        return response()->json([
            'message' => 'Deleted Successfully!',
        ], 200);
    }
}
