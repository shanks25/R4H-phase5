<?php

namespace App\Http\Requests;

use App\Models\TripImport;
use App\Models\ProviderMaster;
use Illuminate\Validation\Rule;
use App\Models\ProviderTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportTripRequest extends FormRequest
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
            'import'=>['required','mimes:csv,txt'],
            'timezone' =>  ['required', Rule::exists('timezone_masters', 'name')],
            'provider_template_id'=>['required', Rule::exists('provider_templates', 'id')],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $metaData = metaData('false', request(), '1009', '', '400', '', $validator->errors());
        throw new HttpResponseException(response()->json($metaData, 422));
    }

    public function passedValidation()
    {
        $this->createTripImport();
    }

    public function createTripImport()
    {
        $file_name =  $this->file('import')->getclientoriginalName();
        $imported_file = uploadCsv($this->file('import'), 'storage/importtrip');

        $trip_import =   TripImport::create([
            'imported_file'=>$imported_file,
            'file_name'=>$file_name,
            'user_id'=>esoId(),
        ]);

        $template =   ProviderTemplate::find(request('provider_template_id'));
        $provider =   ProviderMaster::where('name', $template->name)->where('user_id', esoId())->first();
        $this->merge([
            'trip_import_id' => $trip_import->id,
            'trip_import' => $trip_import,
            'provider_id' => $provider->id,
            'payor_type' => 3,
        ]);
    }
}
