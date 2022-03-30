<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\ValidatePayorIdRule;

class PayorRateStoreRequest extends FormRequest
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
            'payor_type' =>  'required|exists:payor_types,id|numeric',
            'payor_id' => 'required|exists:crm,id|numeric',
            // 'unloaded_rate_per_mile' => 'numeric',
            // 'loaded_rate_per_mile' => 'numeric',
            // 'unloaded_rate_per_min' => 'numeric',
            // 'loaded_rate_per_min' =>'numeric',
            // 'base_rate' => 'numeric',
            // 'unloaded_rate_per_min_base' => 'numeric',
            // 'loaded_rate_per_min_base' => 'numeric',
            // 'wait_time_per_hour' => 'numeric',
            // 'minimum_payout' => 'numeric',
            // 'unloaded_rate_per_mile_base' =>'numeric',
            // 'loaded_rate_per_mile_base' => 'numeric',
            // 'flat_rate' => 'numeric',
            // 'unloaded_rate_per_mile_out' => 'numeric',
            // 'loaded_rate_per_mile_out' => 'numeric',
            // 'unloaded_rate_per_min_out' => 'numeric',
            // 'loaded_rate_per_min_out' => 'numeric',
            // 'base_rate_out' => 'numeric',
            // 'unloaded_rate_per_min_base_out' => 'numeric',
            // 'loaded_rate_per_min_base_out' => 'numeric',
            // 'wait_time_per_hour_out' => 'numeric',
            // 'minimum_payout_out' => 'numeric',
            // 'unloaded_rate_per_mile_base_out' => 'numeric',
            // 'loaded_rate_per_mile_base_out' => 'numeric'
        ];
    }
    protected function failedValidation(Validator $validator) {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
