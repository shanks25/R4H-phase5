<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

use App\Http\Requests\DriverPersonalRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DriverUpdateRequest extends DriverPersonalRequest
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
        'id' => ['required', Rule::exists('driver_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
        'password' => 'required|min:8',
        'password_confirm' => 'same:password'
      ];

        return   $rules =    merge($existing_rules, $new_rules);
    }

    public function messages()
    {
        $existing_msgs =  parent::rules();
        $newmsgs =    [
            'id.required' => 'ID should be number',
            
        ];

        return   merge($existing_msgs, $newmsgs);
    }


    protected function failedValidation(Validator $validator) {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }

}
