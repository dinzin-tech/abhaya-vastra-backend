<?php
namespace App\Http\Traits\Validation;

use App\Models\Products;
use App\Rules\ValidEndDate;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

trait CouponValidation
{
    protected function storeCouponValid($request)
    {    
        $rules = [
            'code' => 'required',
            'discount' => 'required',
            'discount_type' => 'required',
            'usage_limit' => 'required',
            'valid_from' =>'required|date',
            'valid_until' => 'required|date',

        ];
        // if(!tenancy()->tenant){
        //     $rules['service'] = 'required';
        //     $rules['country_id'] = 'required';
        // }   

        $this->validate($request, $rules, [
            'code.required' => 'The code is required.',
            'code.max' => 'The code may not be greater than 50 characters.',
            'code.min' => 'The code must be at least 2 characters.',
            'code.string' => 'The code must be a string.',
            'code.unique' => 'This code has already been taken.',         
        ]);
    }
}

