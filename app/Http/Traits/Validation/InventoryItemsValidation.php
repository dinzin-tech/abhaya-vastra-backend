<?php
namespace App\Http\Traits\Validation;

use App\Models\Products;
use App\Rules\ValidEndDate;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

trait InventoryItemsValidation
{
    protected function storeInventoryItemsValid($request)
    {
        $rules = [
            'product_category' => 'required', 
            'product_name' => 'required|string|regex:/^[a-zA-Z\s]+$/',  
            'brand' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            'base_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',        
            'discount_price' => 'required|numeric|min:0',
            'price_type' => 'required',
            'available_qty' => 'required|integer|min:0', 
            'threshold_quantity' => 'required|integer|min:0',  
            'description' => 'required|string',
        ];

        $this->validate($request, $rules, [
            'product_category.required' => 'The product category is required.',
            'product_name.required' => 'The product name is required.',
            'product_name.string' => 'The product name must be a string.',
            'product_name.regex' => 'The product name may only contain letters and spaces.',
            'brand.required' => 'The brand is required.',
            'brand.string' => 'The brand must be a string.',
            'brand.regex' => 'The brand name may only contain letters and spaces.',
            'base_price.required' => 'The base price is required.',
            'base_price.numeric' => 'The base price must be a valid number.',
            'base_price.min' => 'The base price must be at least 0.',
            'sale_price.required' => 'The sale price is required.',
            'sale_price.numeric' => 'The sale price must be a valid number.',
            'sale_price.min' => 'The sale price must be at least 0.',
            'discount_price.required' => 'The price is required.',
            'discount_price.numeric' => 'The price must be a valid number.',
            'discount_price.min' => 'The price must be at least 0.',
            'price_type.required' => 'The price type is required.',
            'available_qty.required' => 'The available quantity is required.',
            'available_qty.integer' => 'The available quantity must be an integer.',
            'available_qty.min' => 'The available quantity must be at least 0.',
            'threshold_quantity.required' => 'The threshold quantity is required.',
            'threshold_quantity.integer' => 'The threshold quantity must be an integer.',
            'threshold_quantity.min' => 'The threshold quantity must be at least 0.',
            'description.required' => 'The description is required.',
            'description.string' => 'The description must be a string.',
        ]);
    }

}

