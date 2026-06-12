<?php
namespace App\Http\Traits\Validation;

use App\Models\Products;
use App\Rules\ValidEndDate;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

trait PurchaseOrderValidation
{
    protected function storePurchaseOrderValid($request)
    {
        $rules = [
            'vendor' => 'required', 
            'product_category' => 'required', 
            'product_name' => 'required|string|regex:/^[a-zA-Z\s]+$/',  
            'brand' => 'required|string|regex:/^[a-zA-Z\s]+$/', 
            'price' => 'required|numeric|min:0',
            'price_type' => 'required',
            'available_qty' => 'required|integer|min:0', 
            
            'description' => 'required|string',
          
        ];

        $this->validate($request, $rules, [
            'vendor.required' => 'The Vendor is required.',
            'product_category.required' => 'The product category is required.',
            'product_name.required' => 'The product name is required.',
            'product_name.string' => 'The product name must be a string.',
            'product_name.regex' => 'The product name may only contain letters and spaces.',
            'brand.required' => 'The brand is required.',
            'brand.string' => 'The brand must be a string.',
            'brand.regex' => 'The brand name may only contain letters and spaces.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.',
            'price_type.required' => 'The price type is required.',
            'available_qty.required' => 'The available quantity is required.',
            'available_qty.integer' => 'The available quantity must be an integer.',
            'available_qty.min' => 'The available quantity must be at least 0.',
            
            'description.required' => 'The description is required.',
            'description.string' => 'The description must be a string.',
        ]);
    }

}

