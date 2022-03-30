<?php
namespace App\Repository;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TripReccRepo
{

    /**
     * only for recurring trips
     *
     * @param [type] $date_of_service
     * @param [type] $leg
     * @return  object collection
     */
    public function getTripDates()
    {
        $start_date = Carbon::createFromFormat('Y-m-d', request('start_date'));

        $end_date = Carbon::createFromFormat('Y-m-d', request('end_date'));

        // [0=>sunday,6=>saturday]
        $service_dates = request('days');

        $period = CarbonPeriod::create($start_date, $end_date);

        $trip_dates = [];

        foreach ($period as $date) {
            if (in_array($date->dayOfWeek, $service_dates)) {
                $trip_dates[] = $date->format('d-m-Y');
            }
        } ;
        return collect($trip_dates);
    }


    /**
     *  for add recurring trips
     *
     * @param [type] $date_of_service
     * @param [type] $leg
     * @return array
     */
    public function getTripDateTimings($date_of_service, $leg)
    {
        $pickup_time = '';
        if (isset($leg['shedule_pickup_time'])) {
            $pickup_time = $leg['shedule_pickup_time'];
        }
        $dateTime = storeDateTime($date_of_service, $pickup_time, $leg['timezone']);
        $leg['date_of_service']  = $dateTime->format('Y-m-d');
        $leg['week_day']  = $dateTime->dayName;

        if (isset($leg['appointment_time'])) {
            $leg['appointment_time'] = storeDateTime($date_of_service, $leg['appointment_time'], $leg['timezone'])->format('H:i');
        }

        if (isset($leg['shedule_pickup_time'])) {
            $leg['shedule_pickup_time'] = $dateTime->format('H:i');
        }
        return $leg ;
    }

    /**
     *  for edit recurring trips
     *
     * @param [type] $date_of_service
     * @param [type] $leg
     * @return object laravel collections
     */
    public function getExistingTrips($trips, $dates)
    {
        return    $trips->filter(function ($trip) use ($dates) {
            // coverting recurring date to trip timezone date
            $timezone_converted_dates = $dates->map(function ($date) use ($trip) {
                return storeDateTime($date, $trip->shedule_pickup_time, $trip->timezone)->format('Y-m-d');
            });
            return $timezone_converted_dates->contains($trip->date_of_service);
        });
        ;
    }

    /**
     *  for edit recurring trips
     *
     * @param [type] $date_of_service
     * @param [type] $leg
     * @return object laravel collections
     */
    public function getNewDateofServices($dates, $existing_trip_dates)
    {
        // converting d-m-y dates to Y-m-d
        $dates= $dates->map(function ($date) {
            return Carbon::parse($date)->format('Y-m-d');
        });
        
        return   $dates->filter(function ($date) use ($existing_trip_dates) {
            if (in_array($date, $existing_trip_dates)) {
                return false ;
            }
            return true;
        })->values();
    }

    /**
     * ignore columns
     *
     * @return array
     */
    public function ignoreColumns()
    {
        return  ['eso_id','first_name','middle_name','last_name','dob','ssn','pickup_facility_id','pickup_department_name','pickup_address_type_name','drop_facility_id','drop_department_name','drop_address_type_name'];
    }
}
