<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VehicleServiceInvoiceRequest extends FormRequest
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
     * @todo we have to lower case the fields
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    

    public function rules()
    {

     return   $rules =  [
            'service_request_id' => ['required', Rule::exists('vehicle_maintenance_requests', 'id,deleted_at,NULL')->where('user_id', esoId())],
            'invoice_no' => 'required||max:10',
			'service_date' => 'nullable|date_format:Y-m-d',
			'ticket_id' => 'required|max:50',
			'purchase_order' => 'required|max:50',
            'odometter_upon_service' => 'required|numeric|between:0,99999',
            'warranty_information' => 'required|max:150',
			'spacial_instructions' => 'required|max:150',
			'tax' => 'required|numeric|between:0,99.99',
            'sub_total' => 'between:0,99999.99',
            'total' => 'between:0,99999.99',
            'item' => 'required|array|min:1',
            'qty' => 'required|array|min:1',
            'qty.*' => 'numeric|max:5',
            'details' => 'required|array|min:1',
            'amount' => 'required|array|min:1',
            'amount.*' => 'between:0,99999.99',
            'item_total' => 'required|array|min:1',
            'item_total.*' => 'between:0,99999.99',
            
        ];

    }

    public function messages()
    {
        return [
       
        'status' => ' status is required',
          ];
    }

    protected function failedValidation(Validator $validator) {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());

        throw new HttpResponseException(response()->json($metaData, 422));
    }

    
}
