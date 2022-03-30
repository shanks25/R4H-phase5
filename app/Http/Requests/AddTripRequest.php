<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use App\Rules\TripTypeRule;
use App\Models\TimezoneMaster;
use Illuminate\Validation\Rule;
use App\Rules\ValidatePayorIdRule;
use App\Rules\BlockFutureDatesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddTripRequest extends FormRequest
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


    protected function prepareForValidation()
    {
        request()->request->remove('driver_id');
        request()->request->remove('vehicle_id');
        request()->request->remove('invoice_status');
        request()->request->remove('id');
    }

    /**
     * @todo payor_id validation based on payor type
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $before = now()->subYears(100)->format('Y-m-d');

        return [

            'member_id' => [
                'sometimes', 'numeric',
                Rule::exists('members_master', 'id')->where('user_id', esoId())->whereNull('deleted_at')
            ],

            'first_name' => 'required_without:member_id|min:2|alpha',
            'middle_name' => 'alpha|nullable',
            'last_name' => 'required_without:member_id|nullable|alpha',
            'ssn' => 'required_without:member_id',
            'member_phone_no' => 'required_without:member_id|digits_between:10,11',
            'dob' => ['required_without:member_id', 'date_format:Y-m-d', 'before:today',
                    new BlockFutureDatesRule('The dob must be a date before today.'),
                    'after:'.$before
                ],
            'master_level_of_service_id' => ['required', 'numeric', Rule::exists('master_level_of_service', 'id')],
            'additional_passengers' => ['required', 'numeric'],
            'payor_type' =>  ['required', 'numeric',Rule::exists('payor_types', 'id') ],
            'payor_id' =>  ['required', 'numeric', new ValidatePayorIdRule()],


            'legs' =>  ['required', 'array'],
            'legs.*.date_of_service' =>  ['required', 'date_format:Y-m-d'],
            'legs.*.timezone' =>  ['required', 'numeric', Rule::exists('timezone_masters', 'id')],
            'legs.*.appointment_time' =>   'nullable|date_format:H:i',
            'legs.*.shedule_pickup_time' =>   'required_if:legs.*.trip_format,2|date_format:H:i|before:legs.*.appointment_time',
            'legs.*.base_location_id' => ['required', Rule::exists('base_location_master', 'id')->where('user_id', esoId())->whereNull('deleted_at')],

            'legs.*.pickup_address' => ['required'],
            'legs.*.pickup_lat' => ['required', 'between:-90,90'],
            'legs.*.pickup_lng' => ['required', 'between:-180,180'],
            'legs.*.pickup_zip' => ['required', 'digits_between:5,6'],
            'legs.*.pickup_member_address_id' => [
                'numeric',
                Rule::exists('member_addresses', 'id')->where('member_id', $this->member_id)->whereNull('deleted_at')
            ],
            'legs.*.pickup_location_type' => [Rule::in(['1', '2']), 'required_without:pickup_member_address_id'],
            'legs.*.pickup_facility_id' => ['required_if:legs.*.pickup_location_type,2'],
            'legs.*.pickup_department_name' => ['required_if:legs.*.pickup_location_type,2'],
            'legs.*.pickup_crm_contact_no' => ['required_if:legs.*.pickup_location_type,2', 'digits_between:10,11',],
            'legs.*.pickup_address_type_name' => ['required_without:legs.*.pickup_member_address_id',],


            'legs.*.drop_address' => ['required','different:legs.*.pickup_address'],
            'legs.*.drop_lat' => ['required', 'between:-90,90'],
            'legs.*.drop_lng' => ['required', 'between:-180,180'],
            'legs.*.drop_zip' => ['required', 'digits_between:5,6'],
            'legs.*.drop_member_address_id' => ['numeric', Rule::exists('member_addresses', 'id')->where('member_id', $this->member_id)->whereNull('deleted_at')],
            'legs.*.drop_location_type' => [Rule::in(['1', '2']), 'required_without:drop_member_address_id'],
            'legs.*.drop_facility_id' => ['required_if:legs.*.drop_location_type,2'],
            'legs.*.drop_crm_contact_no' => ['digits_between:10,11', 'required_if:legs.*.drop_location_type,2'],
            'legs.*.drop_address_type_name' => ['required_without:legs.*.drop_member_address_id'],
            
            'legs.*.estimated_trip_distance' => ['required','numeric','min:1','max:1000'],
            'legs.*.estimated_trip_duration' => ['required','numeric'],
            'legs.*.estimated_mileage_frombase_location' => ['required','numeric','min:1','max:1000'],
            'legs.*.estimated_duration_frombase_location' => ['required','numeric'],
            'legs.*.trip_price' => ['required','numeric'],
            'legs.*.adjusted_price' => [ 'numeric'],
            'legs.*.total_price' => ['required','numeric'],
            'legs.*.county_type' => ['required','numeric'],
            'legs.*.notes_or_instruction' => [''],

            // 1=normal,2=return,3=will,4=wait
            'legs.*.trip_format' => [Rule::in(['1', '2','3','4']),'present', function ($field, $value, $fail) {
                [$arr, $index] = explode('.', $field);
                if ((int)$index > 0 && !$value) {
                    $fail('trip format is required for ' . $field);
                }
                if ((int)$index > 0 && !in_array($value, [2, 3, 4])) {
                    $fail('pelase enter correct trip format id');
                }
            }]
        ];
    }




    public function messages()
    {
        return [
            'legs.*.shedule_pickup_time.before' => 'The Schedule pickup time should be less than appointment time',
            'legs.*.date_of_service.required' => 'The date of service field is required',
            'legs.*.date_of_service.date_format' => 'The date_of_service does not match the format Y-m-d',
            'legs.*.shedule_pickup_time.required_if' => 'Schedule pickup time is required for return with time trip',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());
        throw new HttpResponseException(response()->json($metaData, 200));
    }


    /**
     *1=normal,2=return,3=will,4=wait
     *
     * @return void
     */
    public function passedValidation()
    {
        $this->merge([
           'user_id' => $this->eso_id,
            'group_id'=>tripGroupId($this),
            'payable_type'=>payorTypeModel($this->payor_type),
           'legs' => $this->upgradeLegData()
        ]);
    }


    public function upgradeLegData()
    {
        $legs = collect($this->legs)->map(function ($leg, $key) {
            $timezone = TimezoneMaster::find($leg['timezone']);
            $leg['timezone'] = $timezone->name;
            $leg['long_timezone'] = $timezone->long_name;
            $leg['short_timezone'] = $timezone->short_name;

            // converting to laravel config timezone (America/Los_Angeles)
            // so recurring trip will not have date of service
            if (isset($leg['date_of_service'])) {
                $dateTime = storeDateTime($leg['date_of_service'], $leg['shedule_pickup_time'], $leg['timezone']);
                $leg['date_of_service']  = $dateTime->format('Y-m-d');
                $leg['week_day']  = $dateTime->dayName;
                $leg['appointment_time'] = storeDateTime($leg['date_of_service'], $leg['appointment_time'], $leg['timezone'])->format('H:i');
                $leg['shedule_pickup_time'] = $dateTime->format('H:i');
            }
           
            
            $leg['trip_no'] = todaysTrip() ."-".($key+1) ;
            // for leg 1 trip format will always be 1
            if ($key == 0) {
                $leg['trip_format'] = 1;
            }

            // there will never be a appointment time for leg 2 and above
            if ($key > 0) {
                unset($leg['appointment_time']);
            }

            //  removing appointment and schedule pickup time for will call and wait time for leg 2 and above
            if ($key > 0 && in_array($leg['trip_format'], [3, 4])) {
                unset($leg['appointment_time'], $leg['shedule_pickup_time']);
            }
            return $leg;
        })->toArray();

        return $legs;
    }
}
