<?php

namespace App\Http\Controllers\Franchise;

use App\Models\City;
use App\Models\BaseLocation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\BaseLocationResource;
use App\Http\Resources\BaseLocationCollection;
use App\Http\Resources\BaseLocationListCollection;
use Illuminate\Validation\Rule;

class BaseLocationController extends Controller
{
    public function index(Request $request)
    {
        $baseLocation = BaseLocation::eso()->get();
        return new BaseLocationCollection($baseLocation);
    }
    public function beseLocationList(Request $request)
    {
        $query=BaseLocation::eso();
        $baseLocation= BaseLocation::filterBaselocation($request, $query);
        $baseLocation= $baseLocation->latest()->paginate(config('Settings.pagination'));
        return  new BaseLocationListCollection($baseLocation);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'state' => ['required',Rule::exists('county_states', 'id')],
        'city_id' => ['required',Rule::exists('county_cities', 'id')->where('state_id',$request->state)],
        'zipcode' => ['required',Rule::exists('county_master', 'zip')->where('state_id',$request->state)->where('city_id',$request->city_id)],
        'address' => 'required|max:300',
        ]);
        
        if ($validator->fails()) {
            return   metaData(false, $request, '3017', '', $error_code = 502, '', $validator->messages());
        }
       
        $request-> merge(['name' =>City::find($request->city_id)->city]);
            
        
        try {
            $baselocation=BaseLocation::Create($request->all());
            $metaData= metaData(true, $request, '3017', 'success', 200, '');
            return (new BaseLocationResource($baselocation))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 3017, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('base_location_master', 'id,deleted_at,NULL')->where('user_id', esoId())]
                
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30018', '', '502', '', $validator->messages());
        }
        
        try {
            $baselocation=BaseLocation::find($request->id);
            $metaData= metaData(true, $request, '30018', 'success', 200, '');
            return (new BaseLocationResource($baselocation))->additional($metaData);
            ;
        } catch (\Exception $e) {
            return metaData(false, $request, 30018, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'id' => ['required',Rule::exists('base_location_master', 'id,deleted_at,NULL')->where('user_id', esoId())],
        'state' => ['required',Rule::exists('county_states', 'id')],
        'city_id' => ['required',Rule::exists('county_cities', 'id')->where('state_id',$request->state)],
        'zipcode' => ['required',Rule::exists('county_master', 'zip')->where('state_id',$request->state)->where('city_id',$request->city_id)],
            'address' => 'required|max:300',
      ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        
        if ($validator->fails()) {
            return   metaData(false, $request, '30019', '', $error_code = 502, '', $validator->messages());
        }
               
     
        $request-> merge(['name' =>City::find($request->city_id)->city]);
                 
        try {
            $baselocation=BaseLocation::find($request->id);
            $baselocation->update($request->except('eso_id'));
            $metaData= metaData(true, $request, '3019', 'success', 200, '');
            return (new BaseLocationResource($baselocation))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 3019, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------delete base location---------------- */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' =>  ['required', Rule::exists('base_location_master', 'id,deleted_at,NULL')->where('user_id', esoId())],
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
        ]);

        if ($validator->fails()) {
            return metaData('false', $request, '30020', '', '502', '', $validator->messages());
        }

        try {
            BaseLocation::find($request->id)->delete();
            $metaData=metaData(true, $request, '30020', 'success', 200, '');
            return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '30020', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    
    /*---------------------End delete base location---------------- */
}
