<?php

namespace App\Http\Controllers\Franchise;

use App\Models\TripMaster;
use Facades\App\Repository\TripRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use App\Http\Requests\EditTripRequest;
use Illuminate\Support\Facades\Validator;
use Facades\App\Repository\TripMemberRepo;

class EditTripController extends Controller
{
    /**
     * edit the trip
     *
     * @param  string  $ability
     * @return string
     */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => ['required',Rule::exists('trip_master_ut', 'id')->whereNull('deleted_at')],
        ]);

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '10009', '', '502', '', $validator->messages());
        }

        $trip =     TripMaster::with(TripRepo::loadTripRelation())->where('id', $request->trip_id)->eso()->first();

        $next_leg = TripMaster::nextLeg($trip)->first();

        //this will tell if you can add new leg while editing this trip
        $trip['i_can_add_leg'] = 1;
        if ($next_leg) {
            $trip['i_can_add_leg'] = 0;
        }
        // this property will help me modify trip resource based for edit trips
        $trip['is_it_for_edit'] = 1;
        return new TripResource($trip);
        $merge =  merge(['data'=> $trip ], metaData(true, $request, '1009', 'success', '', '', ''));
        return response()->json($merge);
    }

    /**
     * update the trip the trip
     *
     * @param  string
     * @return string
     */
    public function update(EditTripRequest $request)
    {
        $main_trip = TripMaster::find($request->trip_id);
        $parent_id =$this->getParentId($main_trip);
        
        /* you cannot add new leg if this trip id is not the last leg  */
        $next_leg = TripMaster::nextLeg($main_trip)->first();
        if ($next_leg && count($request->legs) > 1) {
            return metaData(false, $request, 30002, '', 502, '', 'please add new leg to the last leg the trip');
        }
        try {
            $trip_meta = $request->except('legs') ;
            $trips_collection = [];

            // the trip that is being edited

            foreach ($request->legs as $key => $leg) {
                $leg = (array) TripMemberRepo::getMember($request, $leg);
                $leg['group_id'] =$main_trip->group_id;
                $leg['parent_id'] =$parent_id;
                $leg['trip_no'] =$this->getTripNo($main_trip, $key);
                $data =  merge($trip_meta, $leg);
                /* for the the trip id that is being edited */
                if ($key == 0) {
                    $main_trip->update($data);
                    $main_trip->load(TripRepo::loadTripRelation());
                    $trips_collection[] =  $main_trip ;
                }
                /* for any new leg that is being added to this trip id */
                else {
                    $trip=  TripMaster::create($data);
                    $trips_collection[] =  $trip->load(TripRepo::loadTripRelation());
                }
            }
            $collection = TripResource::collection($trips_collection);
            return    merge(['data'=>$collection], metaData(true, $request, '1009', 'trip updated successfully', '', '', ''));
        } catch (\Exception $e) {
            return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }


    public function getParentId($main_trip)
    {
        if ($main_trip->leg_no == 1) {
            return $main_trip->id;
        }
        return $main_trip->parent_id ;
    }

    /**
     * getTripNo function
     *
     * @param [type] $main_trip
     * @param [type] $key
     * @return void
     */
    public function getTripNo($main_trip, $key)
    {
        if ($key == 0) {
            return $main_trip->trip_no;
        }
      
        return nextTripNo($main_trip->trip_no);
    }
}
