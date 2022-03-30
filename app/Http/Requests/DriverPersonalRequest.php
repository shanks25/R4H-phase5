<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DriverPersonalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @todo we have to lower case the fields
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    

    public function rules()
    {

     return   $rules =  [
        'email' => 'required|email|max:50',
        'mobile_no' => 'required|max:11',
        'password' => 'required|min:8',
        'password_confirm' => 'required|same:password',
        'first_name' => 'required|max:50',
        'middle_name' => 'nullable|max:50',
        'last_name' => 'required|max:50',
        'suffix' => 'nullable|max:50',
        'DOB' => 'nullable|date_format:Y-m-d|before:today',
        'ssn' => 'nullable|max:50',
        'license_no' => 'required|max:50',
        'license_class' => 'required|in:Class A,Class B,Class C,Class D,Class E',
        'license_state' => 'required|alpha|max:4',
        'license_expiry' => 'required|date_format:Y-m-d|after:yesterday',
        'address' => 'required|max:150',
        'second_address' => 'nullable|max:150',
        'address_lng' => 'required',
        'address_lat' => 'required'
        ];

    }

    public function messages()
    {
        return [
       
        'status' => ' status is required',
          ];
    }

    protected function failedValidation(Validator $validator) {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }

    
}
