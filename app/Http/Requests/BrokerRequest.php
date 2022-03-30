<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BrokerRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return  $rules= [
            'name'=>'required|string|min:2|max:50',
            'email'=>'required|email',
            'phone_number'=>'required|numeric|digits_between:10,11',
            'contact_person'=>'required|max:50',
            'contact_phone_number'=>'required|numeric|digits_between:10,11',
            'city'=>'required|string|min:2|max:40',
            'address'=>'required',
            'zipcode'=>'required|numeric|digits_between:5,6',
        ];
         
        
    }

    public function messages()
    {
        return [
        'contact_phone_number.required' => 'The contact person phone number field is required.',
          ];
    }
    protected function failedValidation(Validator $validator) {
    $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

    throw new HttpResponseException(response()->json($metaData, 422));
    }
}
