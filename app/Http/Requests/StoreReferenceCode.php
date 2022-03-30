<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VehicleRequest extends FormRequest
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

        $rules =  [
            'type' => 'required|max:50',
            'Year' => 'required|numeric',
			'model_no' => 'required|max:50',
			'manufacturer' => 'required|max:50',
			'VIN' => 'required|max:50',
            'status' => 'required',
            'license_plate' => 'required|max:50',
			'odometer' => 'required|numeric',
			'documents_file' => 'required|mimes:jpg,bmp,png,pdf,docx',
            'service_id' => 'required|array|min:1',
        ];

    }

    public function messages()
    {
        return [
        'VIN.required' => 'A title is required',
        'VIN.max' => 'send less than 50 brother',
        'status' => ' status is required',
          ];
    }

    protected function failedValidation(Validator $validator) {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }

    
}
