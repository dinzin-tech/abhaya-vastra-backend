<?php
namespace App\Http\Traits\Validation;

use App\Models\Products;
use App\Rules\ValidEndDate;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

trait RegionValidation
{

    /**
     * For validation of Admin/RegionController update() function
     *
     * @param Request $request request body from client
     * @return Validator failed validation response
     * @return Boolean false when all the validations passes
     */
    protected function storeRegionValidAdmin($request)
    {
        $this->validate($request, [
            'name' => 'required|regex:/^[\pL\s\-]+$/u|max:50|min:2',
            'domain' => 'required|max:50|min:2'.($request->id ? '|unique:tenants,data->domain,'.$request->id.',id' : '|unique:tenants,data->domain'),
            'email' => ($request->id ? 'nullable' : 'required|unique:tenants,data->email'),
            'region_name' => 'required|max:50|min:2',
            'password' => ($request->id ? 'nullable' : 'required'),
            'dob' => 'required|date',
            'gender' => 'required',
            'address' => 'required',
            'mobile' => 'required|digits_between:4,15',
        ]);
    }

     /**
     * For validation of Admin/RegionController uploadSingleImage() function
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
