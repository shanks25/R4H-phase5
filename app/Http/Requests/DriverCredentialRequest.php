<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DriverCredentialRequest extends FormRequest
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
      
        'driver_id' => 'required|numeric|exists:driver_master_ut,id,deleted_at,NULL',
        'tlc_license' => 'nullable|max:100',
        'tlc_license_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'upload_tlc' => 'nullable|mimes:jpg,bmp,png,pdf,docx',

        'hippa' => 'nullable|max:100',
        'hippa_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'hippa_upload' => 'nullable|mimes:jpg,bmp,png,pdf,docx',

        'drug_test' => 'nullable|max:100',
        'drug_test_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'upload_drug' => 'nullable|mimes:jpg,bmp,png,pdf,docx',

        'defensive' => 'nullable|max:100',
        'defensive_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'upload_defensive' => 'nullable|mimes:jpg,bmp,png,pdf,docx',

        'oig' => 'nullable|max:100',
        'oig_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'oig_doc' => 'nullable|mimes:jpg,bmp,png,pdf,docx',

        'sam_gov_name' => 'nullable|max:100',
        'sam_gov_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'sam_gov_doc' => 'nullable|mimes:jpg,bmp,png,pdf,docx',
        
        'national_criminal_bk_check_name' => 'nullable|max:100',
        'national_criminal_bk_check_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'national_criminal_bk_check_doc' => 'nullable|mimes:jpg,bmp,png,pdf,docx',

        'sex_offender_report_name' => 'nullable|max:100',
        'sex_offender_report_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'sex_offender_report_doc' => 'nullable|mimes:jpg,bmp,png,pdf,docx',

        'motor_vehicle_record_name' => 'nullable|max:100',
        'motor_vehicle_record_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'motor_vehicle_record_doc' => 'nullable|mimes:jpg,bmp,png,pdf,docx',

        'child_abuse_clearance_name' => 'nullable|max:100',
        'child_abuse_clearance_expiry' => 'nullable|date_format:d-m-Y|after:yesterday',
        'child_abuse_clearance_doc' => 'nullable|mimes:jpg,bmp,png,pdf,docx',

        'cpr' => 'nullable|date_format:d-m-Y',
        'first_aid' => 'nullable|date_format:d-m-Y',
        'mvr' => 'nullable|date_format:d-m-Y',

        'dynamic_identification_id' => 'nullable|array|min:1',
        'dynamic_identification_id.*' => 'exists:driver_identification_master,id',
        'dynamic_identification_expiry_1' => 'nullable|array|min:1',
        'dynamic_identification_expiry_1.*' =>  'date_format:d-m-Y|after:yesterday',
        'dynamic_identification_file_1' => 'nullable',
        'dynamic_identification_file_1.*' =>  'mimes:jpg,bmp,png,pdf,docx',


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
