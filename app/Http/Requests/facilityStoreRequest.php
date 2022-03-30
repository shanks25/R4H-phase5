<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\ValidatePayorIdRule;


class facilityStoreRequest extends FormRequest
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
            'name' => 'required|max:50',
            'city' => 'required',
            'street_address' => 'required',
            'zipcode' => 'required|numeric|digits_between:5,6',
            'email' => 'required|email',
            'crm_mobile_no' => 'required|numeric|digits_between:10,11',
            'representative' => 'required',
            'rep_mobile_no' => 'required|numeric|digits_between:10,11',
            'type' => 'required|exists:payor_types,id',
        ];
    }
    protected function failedValidation(Validator $validator) {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
