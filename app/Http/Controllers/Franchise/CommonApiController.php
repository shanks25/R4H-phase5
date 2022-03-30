<?php

namespace App\Http\Controllers\Franchise;

use App\Models\City;
use App\Models\State;
use App\Models\CountyNames;
use App\Models\CountyMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\VehicleServiceMaster;
use App\Http\Resources\CityCollection;
use App\Http\Resources\StateCollection;
use App\Http\Resources\CommonCollection;
use App\Http\Resources\CountyCollection;
use App\Http\Resources\ZipcodeCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\VehicleServiceMasterCollection;

class CommonApiController extends Controller
{
    public function getState(Request $request)
    {
        $level = State::get();
        return new StateCollection($level);
    }
    
    public function getCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'state_id' => ['required',Rule::exists('county_states', 'id')],
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        
        if ($validator->fails()) {
            return   metaData(false, $request, '30019', '', $error_code = 502, '', $validator->messages());
        }
        $level = City::where('state_id', $request->state_id)->get();
        return new CityCollection($level);
    }

    public function getCounty(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'city_id' => ['required',Rule::exists('county_cities', 'id')],
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        
        if ($validator->fails()) {
            return   metaData(false, $request, '30019', '', $error_code = 502, '', $validator->messages());
        }
        $level = CountyNames::where(array('city_id'=>$request->city_id,'state_id'=>$request->state_id))->get();
        return new CountyCollection($level);
    }

    public function getZipcode(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'state_id' => ['required',Rule::exists('county_master', 'state_id')->where('city_id',$request->city_id)],
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        
        if ($validator->fails()) {
            return   metaData(false, $request, '60004', '', $error_code = 502, '', $validator->messages());
        }
        $level = CountyMaster::where(array('state_id'=> $request->state_id,'city_id'=>$request->city_id))->get();
        return new ZipcodeCollection($level);
    }

    public function getVehicleServices(Request $request){
        try {
            return new VehicleServiceMasterCollection(VehicleServiceMaster::all());
        } catch (\Exception $e) {
            return metaData(false, $request, 30001, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
