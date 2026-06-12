<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\UtilityFunction;
use App\Http\Traits\Validation\ProfileValidation;

class ProfileController extends Controller
{
    use UtilityFunction, ProfileValidation;
    //image upload path set for works images
    protected $imagePath = 'images/users';
    protected $imageSizes = [
        'thumb' => [200,200],
    ];

    /**
     * For showing profile view
     *
     * @return View responce blade view with data
     */
    public function index()
    {
        return view('admin.modules.profile.index');
    }

    public function updateForm(){
        return view('admin.modules.profile.update', [
            "country_codes" => json_decode(\Storage::disk('local')->get('data/country_code_json.json')),
        ]);
    }

    /**
     * For updating profile data
     *
     * @param Request $request request body from client
     * @return Json responce data with http responce code
     */
    public function update(Request $request)
    {
        $valid = $this->updateProfileValidAdmin($request);
        if ($valid) {
            return $valid;
        }

        $data = [
            'name' => $request->name,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'address' => $request->address,
            'country_code' => $request->country_code,
            'mobile' => $request->mobile,
            'profile_image' => $request->image,
        ];
        $user = \App\Models\CentralUser::updateOrCreate(['email' => \Auth::user()->email], $data);
        return response()->json([
            'success' => true,
            'message' => 'Profile Updated',
            'redirect' => "javascript: void(0)",
        ]);
    }

    /**
     * For uploading profile image
     *
     * @param Request $request request body from client
     * @return Json responce data with http responce code
     */
    public function uploadSingleImage(Request $request)
    {
        $valid = $this->uploadSingleImageProfileValidAdmin($request);
        if ($valid) {
            return $valid;
        }
        
        $image = $request->file('image');
        $image_new = "";
        if ($image) {
            $image_new = $this->uploadImage($image, $this->imagePath, $this->imageSizes);
            /*$user = \Auth::user();
            $user->profile_image = $image_new;
            $user->save();*/
            return response()->json([
                'message' => 'Image Uploaded.',
                'thumb_image' => asset('storage/' . $this->imagePath . '/' . $image_new),
                'image' => $image_new,
            ]);

        }
    }
}
