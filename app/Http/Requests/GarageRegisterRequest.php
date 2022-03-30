<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Http\Exceptions\HttpResponseException;

class GarageRegisterRequest extends FormRequest
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
        return [
            'name' => 'required|string|min:2|max:50',
            'email' => 'required|email|unique:garages,email',

        ];
    }


    /**
 * Get the error messages for the defined validation rules.
 *
 * @return array
 */
    // public function messages()
    // {
    //     return [

    //         'name.required' => 'Garage name required',
    //         'name.min' => 'Garage name must have 2 characters',
    //         'name.max' => 'Garage name should not be greater than 50 characters',
    //         'email.required' => 'email field is required',
    //         'email.email' => 'please provide a valid email address',
    //     ];
    // }

    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }

}
