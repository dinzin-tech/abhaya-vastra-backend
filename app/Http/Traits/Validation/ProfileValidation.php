<?php
namespace App\Http\Traits\Validation;

use App\Models\Products;
use App\Rules\ValidEndDate;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

trait ProfileValidation
{

    /**
     * For validation of Admin/PasswordController changePasswordSubmit() function
     *
     * @param Request $request request body from client
     * @return Validator failed validation response
     * @return Boolean false when all the validations passes
     */
    protected function changePasswordSubmitPasswordValidAdmin($request)
    {
        $this->validate($request, [
            'current_pwd' => 'required|min:5',
            'password' => 'required|min:5|max:12|confirmed',
            'password_confirmation' => 'required|max:255',
        ], [
            'current_pwd.required' => 'The Current password is required',
            'current_pwd.min' => 'The Current password should be minimum 5 characters',
            'password.confirmed' => "The Password doesn't match",
        ]);
    }

    /**
     * For validation of Admin/ProfileController update() function
     *
     * @param Request $request request body from client
     * @return Validator failed validation response
     * @return Boolean false when all the validations passes
     */
    protected function updateProfileValidAdmin($request)
    {
        $this->validate($request, [
            'name' => 'required|regex:/^[\pL\s\-]+$/u|max:50|min:2',
            'last_name' => 'max:25',
            'dob' => 'required|date',
            'gender' => 'required',
            'address' => 'required',
            'mobile' => 'required|digits_between:4,15',
        ]);
    }

    /**
     * For validation of Admin/ProfileController uploadSingleImage() function
     *
     * @param Request $request request body from client
     * @return Validator failed validation response
     * @return Boolean false when all the validations passes
     */
    protected function uploadSingleImageProfileValidAdmin($request)
    {
        if ($request->width) {
            $this->validate($request, [
                'image' => 'required|image|mimes:jpeg,png,jpg|max:20480',
            ], [
                'image.image' => 'Image should be JPEG, PNG or JPG',
                'image.mimes' => 'Image should be JPEG, PNG or JPG',
            ]);
        } else {
            $this->validate($request, [
                'image' => 'required|image|mimes:jpeg,png,jpg|max:20480|dimensions:width=' . $request->width . ',height=' . $request->height,
            ], [
                'image.image' => 'Image should be JPEG, PNG or JPG',
                'image.mimes' => 'Image should be JPEG, PNG or JPG',
            ]);
        }
    }
}
