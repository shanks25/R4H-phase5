<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CommonFilterRequest extends FormRequest
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
            'driver_id.*' => 'numeric',
            'base_location_id.*' => 'numeric',
            'level_of_service_id.*' => 'numeric',
            'trip_type.*' => 'numeric',
            'county_type.*' => 'numeric',
            'payor_type' => 'numeric',
            'payor_id.*' => 'numeric',
            'keyword_search' => 'max:50',
        ];
    }
    protected function failedValidation(Validator $validator) {
        $metaData = metaData('false', request(), '4016', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
