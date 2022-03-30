<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AccidentStoreRequest extends FormRequest
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
        'user_id' =>'required|max:50',
        'trip_id' =>'nullable',
        'driver_id' =>'nullable',
        'vehicle_name' => 'nullable',
        'vehicle_id' => 'nullable',
        'added_by' => 'required|max:50',
        'timezone' => 'nullable',
        'date' => 'required|max:50',
        'time' => 'required|max:50',
        'day' => 'required|max:50',
        'weather' => 'required|max:50',
        'location_of_accident' => 'required|max:50',
        'accident_details' => 'required|max:50',
        'your_towing_company' => 'nullable',
        'your_towing_company_phone' => 'required|max:50',
        'other_towing_company' => 'required|max:50',
        'other_towing_company_phone' => 'required|max:50',
        'owner_name' => 'required|max:50',
        'owner_address' => 'required|max:50',
        'owner_phone' => 'required|max:50',
        'other_vehicle_make' => 'nullable',
        'other_vehicle_model' => 'required|max:50',
        'other_vehicle_year' => 'required|max:50',
        'other_vehicle_color' => 'required|max:50',
        'other_license_plate' => 'required|max:50',
        'other_insurance_company' => 'required|max:50',
        'other_agent_name' => 'required|max:50',
        'other_agent_phone' => 'nullable',
        'other_driver_name' => 'required|max:50',
        'other_driver_address' => 'required|max:50',
        'other_driver_phone' => 'required|max:50',
        'police_officer_name' => 'required|max:50',
        'police_officer_phone' => 'required|max:50',
        'police_department' => 'required|max:50',
        'police_badge' => 'required|max:50',
        'police_other_info' => 'required|max:50',
        'witness_name1' => 'required|max:50',
        'witness_address1' => 'required|max:50',
        'witness_home_phone1' => 'required|max:50',
        'witness_work_phone1' => 'required|max:50',
        'witness_name2' => 'required|max:50',
        'witness_address2' => 'required|max:50',
        'witness_home_phone2' => 'required|max:50',
        'witness_work_phone2' => 'required|max:50',
        'sketch' => 'required|max:50',
        'other_license_image' => 'nullable',
        'other_insurance_card_image' => 'nullable',
        'other_license_plate_image' => 'nullable',
        'your_vehicle_passengers' => 'nullable',
        'your_vehicle_passengers_injuries' => 'nullable',
        'other_vehicle_passengers' => 'nullable',
        'other_vehicle_passengers_injuries' => 'nullable',
        'accident_image' => 'nullable',
        'video' => 'nullable',

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
