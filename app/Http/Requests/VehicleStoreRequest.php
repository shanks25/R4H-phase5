<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VehicleStoreRequest extends FormRequest
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
            'type' => 'required|in:Personal Vehicle,Company Owned',
            'Year' => 'required|digits:4|integer|min:1900|max:'.(date('Y')),
			'model_no' => 'required|max:50',
			'manufacturer' => 'required|max:50',
			'VIN' => 'required|max:50',
            'status' => 'required|in:0,1',
            'license_plate' => 'required|max:50',
			'odometer' => 'required|numeric',
			'documents_file' => 'required|mimes:jpg,bmp,png,pdf,docx',
            'service_id' => 'array|min:1',
            'service_id.*' => 'required|numeric|exists:master_level_of_service,id',
            'odometer_start_date' => 'nullable|date_format:Y-m-d',
            'insurance_expiry_date' => 'nullable|date_format:Y-m-d|after:yesterday',
            'registration_expiry_date' => 'nullable|date_format:Y-m-d|after:yesterday',
            'miles_per_gallon' => 'nullable|numeric',
            
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
