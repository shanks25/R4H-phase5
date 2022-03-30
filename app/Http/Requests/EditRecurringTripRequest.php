<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Rules\RecurringTripCountRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class EditRecurringTripRequest extends FormRequest
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
        $rules = [
            'recurring_id' => ['required',Rule::exists('trip_recurring_master', 'id')->where('user_id', esoId())->whereNull('deleted_at')],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d','after_or_equal:start_date'],
            'days' => ['required', 'array'],
            
        ];

        if (request('start_date') && request('end_date')) {
            $rules['days'] = ['required','array',Rule::in(range(0, 6)),new RecurringTripCountRule()];
        }

        return $rules;
    }


    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());
        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
