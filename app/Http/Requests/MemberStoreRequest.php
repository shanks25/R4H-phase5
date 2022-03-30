<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MemberStoreRequest extends FormRequest
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

       return $rules =  [
            'first_name' => 'required|max:50',
            'middle_name' => 'nullable|max:50',
			'last_name' => 'required|max:50',
			'mobile_no' => 'required|max:11|numeric',
			'dob' => 'nullable|date_format:Y/m/d',
            'ssn' => 'unique:members_master,ssn,NULL,id,deleted_at,NULL',
            'emergency_contact_name' => 'max:50',
			'emergency_contact' => 'nullable|max:11|numeric',
			'isi' => 'max:50',
            'mode_of_transport' => 'nullable|numeric',  
            'no_show_raw' => 'nullable|max:100',
            'was_confirmed' => 'nullable|max:100',
            'minor' => 'max:100',
            'auto_calculation' => 'nullable|max:100',
            'trip_purpose' => 'nullable|max:100',
            'personal_notes' => 'nullable|max:100',
            'email' => 'nullable|email|max:50',
            'promo_emails' => 'in:yes,no',
            'instructions' => 'nullable|max:100',
            'primary_payor_type' => 'required|numeric',
            
             'address_type' => 'required|array|min:1',
            'street_address' => 'required|array|min:1',
            'zipcode' => 'required|array|min:1',
            'location_type' => 'nullable|array|min:1',
            'facility_autofill' => 'nullable|array|min:1',
            'longitude' => 'nullable|array|min:1',
            'department' => 'nullable|array|min:1',
            'latitude' => 'nullable|array|min:1',
            'weight' => 'nullable|numeric',
            'is_contract' => 'in:1,2',  
            'active' => 'in:yes,no',  
            'wrong_number' => 'in:yes,no',       
            'ride_alone' => 'in:yes,no',  
            'call_disabled' => 'in:yes,no',  
            'phone_auto_update' => 'in:yes,no',  
            'on_hold' => 'in:yes,no',  
            'gender' => 'in:female,male',  
            'need_attendant' => 'in:yes,no',  
           
           
           
        ];
    }

    public function messages()
    {
        return [
       
        'status' => ' status is required',
          ];
    }

    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '30022', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
