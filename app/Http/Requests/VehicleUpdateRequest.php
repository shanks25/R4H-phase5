<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Http\Requests\VehicleStoreRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VehicleUpdateRequest extends VehicleStoreRequest
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
        'id' => ['required', Rule::exists('vehicle_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
        'documents_file' =>'nullable|mimes:jpg,bmp,png,pdf,docx',
        
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
