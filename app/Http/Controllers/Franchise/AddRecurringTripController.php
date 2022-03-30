<?php

namespace App\Http\Controllers\Franchise;

use App\Models\TripMaster;
use App\Models\TripRecurringMaster;
use App\Http\Controllers\Controller;
use App\Http\Resources\TripResource;
use Facades\App\Repository\TripRepo;
use Facades\App\Repository\TripReccRepo;
use Facades\App\Repository\TripMemberRepo;
use App\Http\Requests\AddRecurringTripRequest;

class AddRecurringTripController extends Controller
{
    public function index(AddRecurringTripRequest $request)
    {
       
        // return $request ;

        $dates = TripReccRepo::getTripDates();
        $days =  weekDays($request->days); /* monday,friday comma seperated days */
        $request->merge(['weekdays'=>$days,'master_key'=>uniqid(),'days'=>implode($request->days, ",")]);
        $recurring_master =  TripRecurringMaster::create($request->all());


        $all_recurring_trips_collection = [];
        foreach ($dates as $key1 => $date_of_service) {
            $trips_collection = [];
            $trip_meta = $request->except('legs') ;
           
            foreach ($request->legs as $key => $leg) {
                $leg = (array)  TripReccRepo::getTripDateTimings($date_of_service, $leg);
                $leg = (array) TripMemberRepo::getMember($request, $leg);
                $leg['group_id'] =$request->group_id;
                $data =  merge($trip_meta, $leg);
                $data['leg_no'] = ($key+1);
                $data['parent_id'] = TripRepo::getParentId($trips_collection, $key);
                $data['recurring_master_id'] = $recurring_master->id;

                $trip =  TripMaster::create($data);
                $trips_collection[] =  $trip->load(TripRepo::loadTripRelation());
                $all_recurring_trips_collection[$key1]=  $trips_collection;
            }
        }

        $trips = collect($all_recurring_trips_collection)->collapse();
        $recurring_master->update(['trip_count'=>$trips->count()]) ;

        $recurring_trips_collection = TripResource::collection($trips);
        $recurring_master['trips'] =  $recurring_trips_collection;
        $data['data'] = $recurring_master;
        $merge =  merge(['data'=>$recurring_master], metaData(true, $request, '1009', 'Recurring trip added successfully', '', '', ''));

        return response()->json($merge);
    }
}
