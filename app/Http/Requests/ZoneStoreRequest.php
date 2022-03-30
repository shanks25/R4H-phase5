<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ZoneStoreRequest extends FormRequest
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
        return $rules= [
            'name'=>'required|unique:zone_master|string|min:2|max:50',
            'state.*'=>'required|numeric|exists:zone_states,state_id',
            'city.*'=>'required|numeric|exists:zone_cities,city_id', 
            'county.*'=>'required|numeric|exists:zone_counties,county_id',
            'zipcode.*'=>'required|numeric|exists:zone_zips,zipcode_id',
        ];
    }

    public function messages()
    {
        return [
       
        'name.required' => 'The zone name field is required',
        'state.*.required'=>'The state field is required',
        'city.*.required'=>'The city field is required',
        'county.*.required'=>'The county field is required',
        'zipcode.*.required'=>'The zipcode field is required',
          ];
    }

    protected function failedValidation(Validator $validator) {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }
}
