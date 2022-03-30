<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateLogRequest extends FormRequest
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
            'trip_id' => 'required|numeric',
            // 'start_time' => 'timezone',
        ];
    }
    public function messages()
    {
        return [
            'trip_id.required' => 'trip_id is required',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '20009', '', '403', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
