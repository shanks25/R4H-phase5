<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class EditTripRequest extends AddTripRequest
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
            'trip_id' =>['required', Rule::exists('trip_master_ut', 'id')->where('user_id', esoId())],
            'legs.*.trip_format' => ['present',Rule::in(['1', '2','3','4']),],
        ];

        return   $rules =    merge($existing_rules, $new_rules);
    }

    public function messages()
    {
        $existing_msgs =  parent::rules();
        $newmsgs =    [];

        return   merge($existing_msgs, $newmsgs);
    }
}
