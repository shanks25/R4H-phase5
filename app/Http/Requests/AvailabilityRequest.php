<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AvailabilityRequest extends FormRequest
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
        'id' => 'required|numeric|exists:driver_master_ut,id,deleted_at,NULL',
        'start_date' => 'required|array|min:1',
        'start_date.*' => 'date_format:Y-m-d H:i:s|after:yesterday',
        'end_date' => 'required|array|min:1',
        'end_date.*' =>'date_format:Y-m-d H:i:s|after:yesterday',
        'status' => 'required|in:1,2,3',
        'resume_date' => 'nullable|date_format:Y-m-d|after:yesterday',
        'reason' => 'required|max:150',
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
