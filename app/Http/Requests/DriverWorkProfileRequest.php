<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DriverWorkProfileRequest extends FormRequest
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
        'work_timing' => 'required|date_format:H:i:s',
        'zone' => 'required|array|min:1',
        'timezone' => 'required',
        'allow_vehicle_home' => 'nullable|in:yes,no',
        'stretcher' => 'nullable|in:yes,no',
        'can_overright' => 'nullable|in:yes,no',
        'fav_passenger' => 'nullable|in:yes,no',
        'paralift' => 'nullable|in:yes,no',
        'weight_limitation' => 'nullable|in:yes,no',
        'assistant' => 'nullable|in:yes,no',
        'training_video' =>  'nullable|in:yes,no',
        'allowed_hours' =>  'nullable|max:150',
        'ability_get_trips' =>  'nullable|max:150',
        'attendant' =>  'nullable|in:yes,no,',
        'central_registry' =>  'nullable|max:150',
        'fingerprint' =>  'nullable|max:150',
        'enabled_cancel' =>  'nullable|in:yes,no',
        'route_sequence' =>  'nullable|in:yes,no',
        'decline_orders' =>  'nullable|in:yes,no',
        'bbp' =>  'nullable|max:150',
        'exit_date' =>  'nullable|date_format:d-m-Y|after:yesterday',
        'termination_reason' =>  'nullable|max:150',
        'notes' =>  'nullable|max:250',
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
