<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactUs;

class ContactUsController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.modules.contact_us.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    public function listContact(Request $request)
    {
        $query = ContactUs::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('email', 'like', "%{$request->q}%")
                  ->orWhere('phone', 'like', "%{$request->q}%")
                  ->orWhere('address', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        return response()->json([
            'rows'       => view('admin.modules.contact_us.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ]);
    }

    public function create()
    {
        return view('admin.modules.contact_us.add', ['item' => false]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'   => 'required|email',
            'phone'   => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        if ($request->id) {
            $contact = ContactUs::findOrFail($request->id);
            $contact->update($request->only(['email','phone','address']));
            $message = 'Contact Details Updated';
        } else {
            ContactUs::create($request->only(['email','phone','address']));
            $message = 'Contact Details Added';
        }

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => route('contact-us.index')
        ]);
    }

    public function edit($id)
    {
        $item = ContactUs::findOrFail($id);
        return view('admin.modules.contact_us.add', ['item' => $item]);
    }

    public function delete(Request $request)
    {
        $contact = ContactUs::findOrFail($request->id);
        $contact->delete();

        return response()->json(['message' => 'Deleted Successfully!'], 200);
    }
}
