<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Rules\ValidatePayorIdRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ValidateAutoSetRequest extends FormRequest
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
            'payor_type' =>  ['required', 'numeric',Rule::notIn(['1']),Rule::exists('payor_types', 'id') ],
            'payor_id' =>  ['required', 'numeric', new ValidatePayorIdRule()],
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());
        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
