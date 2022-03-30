<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DriverProfessionalRequest extends FormRequest
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
        'id' => ['required', Rule::exists('driver_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
        'employee_id' => 'required|max:50',
        'driver_type' => 'required|in:Company,ISP',
        'position' => 'required',
        'department_code' => 'required',
        'driving_experience' => 'required',
        'work_start_date' => 'required|date_format:Y-m-d',
        'hire_date' => 'required|date_format:Y-m-d',
        'work_status' => 'required|in:0,1',
        'insurance_status' => 'required|in:0,1',
        'insurance_id' => 'required',
        'status' => 'in:0,1',
        'upload_signature' =>  'required|mimes:jpg,bmp,png,pdf,docx',
        'service_id' => 'nullable|array|min:1',
        'service_id.*' =>  'numeric|max:3',
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
