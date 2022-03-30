<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ZoneMaster;
use App\Models\ZoneCities;
use App\Models\ZoneStates;
use App\Models\ZoneCountries;
use App\Models\ZoneZip;
use App\Models\CountyMaster;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ZoneStoreRequest;
use App\Http\Requests\ZoneUpdateRequest;
use App\Http\Resources\ZoneResource;
use App\Http\Resources\ZoneCollection;

class ZonesController extends Controller
{
    public function index()
    {
    }
    
    public function store(ZoneStoreRequest $request)
    {
        $data['name'] = $request->name;
        $data['user_id'] = $request->eso_id;
        $zones = ZoneMaster::create($data);
        // print_r($zones);die;
        //store driver state
        $states = $request->state;
        foreach ($states as $state) {
            $driver = new ZoneStates();
            $driver->state_id = $state;
            $driver->zone_id = $zones->id;
            $driver->save();
        }
        //close state 
        // start cities 
        $cities = $request->city;
        foreach ($cities as $ccities) {
            $driver = new ZoneCities();
            $driver->city_id = $ccities;
            $driver->zone_id = $zones->id;
            $driver->save();
        }
        // start counties 
        $counties = $request->county;
        foreach ($counties as $ccounties) {
            $driver = new ZoneCountries();
            $driver->county_id = $ccounties;
            $driver->zone_id = $zones->id;
            $driver->save();
        }
        // store zipcode
        $zips = $request->zipcode;
        $zip_detail = CountyMaster::whereIn('id', $zips)->get();
        foreach ($zip_detail as $dtl) {
            $derviceZip = new ZoneZip();
            $derviceZip->zone_id = $zones->id;
            $derviceZip->zipcode_id = $dtl->id;
            $derviceZip->zipcode = $dtl->zip;
            $derviceZip->save();
        }
        return response()->json(['status' => 1, 'msg' => 'Zone Added Successfully']);
    }


    function getzip(Request $request)
    {   
       
        try{     
        $payor = ZoneMaster::with('county','state','cities','zip')->where('user_id',$request->eso_id)->get();
        return $payor; 
      
        return (new ZoneCollection($payor))->additional(metaData(true, $request, '1008', 'success', '', '', ''));
        
        }catch (\Exception $e) {
            return metaData(false, $request, 5001, '', 502, errorDesc($e), 'Error occured in server side');
        }
    }

    
    public function editZones(Request $request)
    {    
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:zone_master,id,deleted_at,NULL',
            
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '4003', '', '502', '', $validator->messages());
        }  

        try {
            $zone = ZoneMaster::with('county','zip','state','cities')->where('user_id',$request->eso_id)->where('id',$request->id)->first();
            $metaData= metaData(true, $request, '4003', 'success', 200, '');
            return (new ZoneResource($zone))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30009, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function zoneUpdate(ZoneUpdateRequest $request)
    {
        $data['name'] = $request->zone_name;
        $data['user_id'] = $request->eso_id;

        $zones = ZoneMaster::find($request->edit_zone_id);
        $zones->name = $request->zone_name;
      
        $zones->save();
       
        ZoneStates::where('zone_id', $request->edit_zone_id)->delete();
        $states = $request->state;
        foreach ($states as $state) {
            $driver = new ZoneStates();
            $driver->state_id = $state;
            $driver->zone_id = $request->edit_zone_id;
            $driver->save();
        }
        //close state 
        // start cities 
        ZoneCities::where('zone_id', $zones->id)->delete();
        $cities = $request->city;
        foreach ($cities as $ccities) {
            $driver = new ZoneCities();
            $driver->city_id = $ccities;
            $driver->zone_id = $zones->id;
            $driver->save();
        }
        // start counties 
        ZoneCountries::where('zone_id', $zones->id)->delete();
        $counties = $request->county;
        foreach ($counties as $ccounties) {
            $driver = new ZoneCountries();
            $driver->county_id = $ccounties;
            $driver->zone_id = $zones->id;
            $driver->save();
        }
        // store zipcode
        ZoneZip::where('zone_id', $zones->id)->delete();
        $zips = $request->zipcode;
        $zip_detail = CountyMaster::whereIn('id', $zips)->get();
        foreach ($zip_detail as $dtl) {
            $derviceZip = new ZoneZip();
            $derviceZip->zone_id = $zones->id;
            $derviceZip->zipcode_id = $dtl->id;
            $derviceZip->zipcode = $dtl->zip;
            $derviceZip->save();
        }
        return response()->json(['status' => 1, 'msg' => 'Zone Update Successfully']);
    }
    
    
    public function delete(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|numeric|exists:zone_master,id,deleted_at,NULL',
            
             ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
            ]);
        
            if ($validator->fails()) {
            return metaData('false', $request, '5004', '', '504', '', $validator->messages());
            }

        try {
                
           $zone = ZoneMaster::find($request->zone_id);
           $zone->state()->delete();
           $zone->county()->delete();
           $zone->zip()->delete();
           $zone->cities()->delete();
           ZoneMaster::find($request->zone_id)->delete();
            $metaData=metaData(true, $request, '4005', 'success', 200, '');
            return  merge($metaData, ['data'=>['deleted_id'=>$request->zone_id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '4005', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }



}
