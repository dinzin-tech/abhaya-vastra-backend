<?php
namespace App\Http\Traits\Validation;

use App\Models\Products;
use App\Rules\ValidEndDate;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

trait AccountantValidation
{
    protected function storeAccountantValid($request)
    {
        $rules = [
            'name' => 'required|max:50|min:2|string|regex:/^[a-zA-Z\s]+$/u',
            'dob' => 'required|date',
            'gender' => 'required',
            'address' => 'required|string|max:255',
            'mobile' => 'required|digits_between:4,15|regex:/^[0-9]{4,15}$/',
        ];
        if(!tenancy()->tenant){
            $rules['service'] = 'required';
            $rules['country_id'] = 'required';
        }
        if (empty($request->id)) {
            $rules['email'] = 'required|string|email|max:255|unique:users';
            $rules['password'] = 'required|min:5|max:12';
        }

        $this->validate($request, $rules, [
            'name.required' => 'The name is required.',
            'name.max' => 'The name may not be greater than 50 characters.',
            'name.min' => 'The name must be at least 2 characters.',
            'name.string' => 'The name must be a string.',
            // 'name.unique' => 'This name has already been taken.',
            'email.required' => 'The email is required.',
            'email.unique' => 'This email has already been taken.',
            'password.required' => 'The password is required.',
            'password.max' => 'The password must be atleast 5 characters.',
        ]);
    }

    protected function uploadSingleImageProfileValidAccountant($request)
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

