<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    

    public function index(Request $request)
    {
        return view('admin.modules.users.list', [
            'q'      => $request->q,
            'offset' => $request->offset,
            'name'  => $request->name,
            'phone' => $request->phone,
            'emaili;' => $request->email,
            'address' => $request->address
        ]);
    }

    public function listUsers(Request $request)
    {
        $query = User::query(); // Use query builder, not User::all()
        $offset = $request->offset ?? 10;

        if (!empty($request->name)) {
            $query->where('name', 'like', "%{$request->name}%");
        }
        if (!empty($request->email)) {
            $query->where('email', 'like', "%{$request->email}%");
        }
        if (!empty($request->phone)) {
            $query->where('phone', 'like', "%{$request->phone}%");
        }
        if (!empty($request->address)) {
            $query->where('address', 'like', "%{$request->address}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        // Wrap response in 'success' and 'data' so JS works without changes
        return response()->json([
            'success' => true,
            'data'    => $items->items(), // array of users
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ]);
    }


}
