<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Utility\ValidationUtil;
use App\Models\User;
use Auth;
use Hash;

class PasswordController extends Controller
{
    //use ValidationUtil;
    /**
     * This method is used to show change password form
     */
    public function changePassword()
    {
        return view('admin.modules.password.index');
    }

    /**
     * This method is used to change password
     * @param $request - Post data from html form
     */
    public function changePasswordSubmit(Request $request)
    {
        $valid = $this->changePasswordSubmitPasswordValidAdmin($request);
        if ($valid) {
            return $valid;
        }

        $user = User::find(Auth::guard()->id());
        if (Hash::check($request->current_pwd, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'Password Changed',
                'redirect' => "javascript: void(0)",
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Wrong current password',
                'redirect' => "javascript: void(0)",
            ]);
        }

    }
}