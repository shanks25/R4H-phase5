<?php

namespace App\Http\Controllers\Franchise;

use Carbon\Carbon;
use App\Models\TripMaster;
use App\Models\TripRecurringMaster;
use App\Http\Controllers\Controller;
use Facades\App\Repository\TripReccRepo;
use App\Http\Requests\EditRecurringTripRequest;

class EditRecurringTripController extends Controller
{
    public function index(EditRecurringTripRequest $request)
    {
        $recurring_master =  TripRecurringMaster::with('trips:id,trip_no,recurring_master_id,date_of_service,appointment_time,shedule_pickup_time,status_id,leg_no')->find($request->recurring_id);
        $dates = TripReccRepo::getTripDates();
        
        $trips = $recurring_master->trips;

        // get all the trips which we dont need to edit
        $existing_trips =  TripReccRepo::getExistingTrips($trips, $dates);


        $trips_to_delete =  $trips ->whereNotIn('id', $existing_trips->pluck('id'))
                                   ->whereIn('status_id', TripMaster::nonOperationalTrips())
                                   ->values() ;
        // delete all non dates and non operational trips
        $trips_to_delete->each->delete();

        $existing_trip_dates =  $existing_trips->pluck('date_of_service')->toArray();
        


        //filtering newly generated dates from existing trip
        $new_date_of_services =  TripReccRepo::getNewDateofServices($dates, $existing_trip_dates);


        // using newly generated dates to create trips
        foreach ($new_date_of_services as $key1 => $date_of_service) {
            $trips =  TripMaster::withTrashed()->massAssignColumns(TripReccRepo::ignoreColumns())
                        ->where('recurring_master_id', $request->recurring_id)
                        ->where('leg_no', 1)->first();
          

            $new_trip = $trips->replicate();
            $new_trip->parent_id = 0;
            $new_trip->date_of_service = $date_of_service ;
            $new_trip->recurring_master_id = $recurring_master->id ;
            $new_trip->save();
    
            foreach ($trips->downLegs as $key => $trip) {
                $leg = $trip->replicate();
                $leg->parent_id = $new_trip->id;
                $leg->date_of_service = $date_of_service ;
                $new_trip->recurring_master_id = $recurring_master->id ;
                $leg->save();
            }
        }
        
        $recurring = $this->updateRecurringMaster($request);
        $merge =  merge(['recurring'=>$recurring], metaData(true, $request, '1009', 'Recurring updated successfully', '', '', ''));
        return response()->json($merge);
    }

    /**
     * updating recurring master
     *
     * @param [type] $request
     * @return object
     */
    public function updateRecurringMaster($request)
    {
        // fetching this again because i need updated trips for this recurring
        $recurring =   TripRecurringMaster::with('trips')->find($request->recurring_id);
        $recurring->weekdays = weekDays($request->days);
        $recurring->days = implode($request->days, ",");
        $recurring->trip_count = $recurring->trips->count();
        $recurring->start_date = $request->start_date;
        $recurring->end_date = $request->end_date;
        $recurring->save();
        return $recurring ;
    }
}
