<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GarageUpdateRequest extends GarageRegisterRequest
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
        $existing_rules = parent::rules();
        $new_rules = [
            'id' => ['required', Rule::exists('garages', 'id,deleted_at,NULL')->where('user_id', esoId())],
            'email' => ['required', Rule::unique('garages')->ignore($this->id)]
        ];
        return $rules = merge($existing_rules, $new_rules);
    }

    public function messages()
    {
        $existing_messages = parent::rules();
        $new_messages = [
            'id.required' => 'id is required '
        ];
        return merge($existing_messages, $new_messages);
    }

    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
