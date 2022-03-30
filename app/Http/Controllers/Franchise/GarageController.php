<?php

namespace App\Http\Controllers\Franchise;

use App\Models\Garage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\GarageResource;
use App\Http\Resources\GarageCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\GarageRegisterRequest;
use App\Http\Requests\GarageUpdateRequest;

class GarageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $garage = Garage::latest()->paginate(config('Settings.pagination'));
            return new GarageCollection($garage);
        } catch (\Exception $e) {
            return metaData(false, $request, 30001, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    /*---------------------End Garage List---------------- */
    

    /*--------------------- Add Garage---------------- */
    public function store(GarageRegisterRequest $request)
    {
        // return $request->all();
        try {   
            $garage =   Garage::create($request->all());

            $metaData = metaData(true, $request, '3002', 'success', 200, '');
            return (new GarageResource($garage))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }   
    /*---------------------End Add Garage---------------- */
    


    /*---------------------edit Garage---------------- */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('garages', 'id,deleted_at,NULL')->where('user_id', esoId())],
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
        ]);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30003', '', '502', '', $validator->messages());
        }
        try {
            $garage = Garage::find($request->id);
            $metaData = metaData(true, $request, '30003', 'success', 200, '');
            return (new GarageResource($garage))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30003, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
 /*------------------------End edit Garage---------------- */


    /*---------------------Update Garage---------------- */
    public function update(GarageUpdateRequest $request)
    {
        try {
            $input = $request->except('eso_id');
            $garage = Garage::where('id', $request->id)->update($input);
            $updatedGarage = Garage::find($request->id);
            $metaData = metaData(true, $request, '3004', 'success', 200, '');
            return (new GarageResource($updatedGarage))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '30004', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }


    /*---------------------delete Garage---------------- */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('garages', 'id,deleted_at,NULL')->where('user_id', esoId())]
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',

        ]);

        if ($validator->fails()) {
            return metaData('false', $request, '30003', '', '502', '', $validator->messages());
        }

        try {
            Garage::find($request->id)->delete();
            $metaData = metaData(true, $request, '3004', 'success', 200, '');
            return  merge($metaData, ['data' => ['deleted_id' => $request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '30005', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
