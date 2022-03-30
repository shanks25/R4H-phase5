<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVehicleMaintenanceRequest extends FormRequest
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
            'vehicle_id' => ['required', Rule::exists('vehicle_master_ut', 'id')->where('user_id', esoId())->whereNull('deleted_at')],
             
            // 'VIN' => 'required',
            'request_date' => 'required',
            'driver_id' => ['required', Rule::exists('driver_master_ut', 'id')->where('user_id', esoId())->whereNull('deleted_at')],
             
            'mileage' => 'required',
            'service_details' => 'required|array',
       
        ];
    }

    // public function messages()
    // {
    //     return [
    //         'vehicle_id.required' => 'Vehicle Id is required',
    //         'request_date.required' => 'Request date is required',
    //         'driver_id.required' => 'Driver id is required',
    //         'mileage.required' => 'Mileage is required',
    //         'service_details.required' => 'Service details required',
    //     ];
    // }

    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }

    public function passedValidation()
    {
        $this->merge(['maintenance_type' => 'manual']);
    }
}
