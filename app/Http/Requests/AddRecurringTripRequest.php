<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Rules\RecurringTripCountRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddRecurringTripRequest extends AddTripRequest
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
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d','after_or_equal:start_date'],
            'days' => ['required','array',Rule::in(range(0, 6)),new RecurringTripCountRule()],
            'legs.*.date_of_service' => [''],
        ];

        return   $rules =    merge($existing_rules, $new_rules);
    }

    public function messages()
    {
        $existing_msgs =  parent::rules();
        $newmsgs =    [];

        return   merge($existing_msgs, $newmsgs);
    }

    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());
        throw new HttpResponseException(response()->json($metaData, 200));
    }
}
