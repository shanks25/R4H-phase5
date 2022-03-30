<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Http\Exceptions\HttpResponseException;


class updateFacilityRequest extends facilityStoreRequest
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
        $existing_rules =  parent::rules();

        $new_rules= [
            'id' => 'required|numeric|exists:crm,id,deleted_at,NULL'
      ];

        return   $rules =    merge($existing_rules, $new_rules);
    }
    public function messages()
    {
        $existing_msgs =  parent::rules();
        $newmsgs =    [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
        ];

        return   merge($existing_msgs, $newmsgs);
    }


    protected function failedValidation(Validator $validator) {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
