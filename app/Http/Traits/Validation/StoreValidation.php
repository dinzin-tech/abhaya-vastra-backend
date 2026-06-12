<?php
namespace App\Http\Traits\Validation;

use App\Models\Products;
use App\Rules\ValidEndDate;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

trait StoreValidation
{


    /**
     * For validation of Tenanat/StoreController
     *
     * @param Request $request request body from client
     * @return Validator failed validation response
     * @return Boolean false when all the validations passes
     */
    protected function storeRegionValid($request)
    {
        $this->validate($request, [
            'name' => 'required|max:50|min:4',
            'email' => ($request->id ? 'nullable' : 'required|email|unique:tenants,data->email'),
            'country_code' => 'required',
            'mobile' => 'required|digits:10|regex:/^[0-9]{10}$/',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i|after:open_time',
            'gst_number' => 'nullable|string|max:15',
            'state' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'pincode' => 'required|digits:6',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'The store name is required.',
            'name.max' => 'The store name must not exceed 50 characters.',
            'name.min' => 'The store name must be at least 4 characters long.',
    
            'email.required' => 'The email address is required.',
            'email.email' => 'The email address must be a valid email format.',
            'email.unique' => 'The email address has already been taken.',
    
            'country_code.required' => 'The country code is required.',
    
            'mobile.required' => 'The mobile number is required.',
            'mobile.digits_between' => 'The mobile number must be 10 digits.',
            'mobile.regex' => 'The mobile number must only contain numbers.',
    
            'open_time.required' => 'The opening time is required.',
            'open_time.date_format' => 'The opening time must be in the format HH:MM.',
    
            'close_time.required' => 'The closing time is required.',
            'close_time.date_format' => 'The closing time must be in the format HH:MM.',
            'close_time.after' => 'The closing time must be after the opening time.',
    
            'gst_number.string' => 'The GST number must be a string.',
            'gst_number.max' => 'The GST number must not exceed 15 characters.',
    
            'state.required' => 'The state is required.',
            'state.max' => 'The state name must not exceed 50 characters.',
    
            'city.required' => 'The city is required.',
            'city.max' => 'The city name must not exceed 50 characters.',
    
            'pincode.required' => 'The pincode is required.',
            'pincode.digits_between' => 'The pincode must be 6 digits.',
    
            'address.required' => 'The address is required.',
            'address.string' => 'The address must be a valid string.',
            'address.max' => 'The address must not exceed 255 characters.',
    
            'description.string' => 'The description must be a valid string.',
            'description.max' => 'The description must not exceed 500 characters.',
        ]);
    }

}

