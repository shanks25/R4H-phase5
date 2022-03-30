<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\PreventsRedirectWhenFailedTrait;

class ProfileRequest extends FormRequest
{

    // use PreventsRedirectWhenFailedTrait;

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
            'name' => 'required',
            'mobile_no' => 'required',
            'representative_name' => 'required',
            'address' => 'required',
            'timezone' => 'required',
        ];
    }

    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //             $validator->errors()->add('field', 'Something is wrong with this field!');
    //     });
    // }

    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
