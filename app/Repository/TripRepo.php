<?php
namespace App\Repository;

class TripRepo
{
    public function loadTripRelation()
    {
        return [
            'pickupDetails:id,address_name,street_address,location_type,facility_autofill_id,department,department_name',
            'dropDetails:id,address_name,street_address,location_type,facility_autofill_id,department,department_name',
            'levelOfService:id,name',
            'baselocation:id,name',
            'payorTypeNames:id,name',
            'payor:id,name',
            'member:id,first_name,middle_name,last_name,ssn,dob,phone_number',
            'member.addresses:id,member_id,address_type,address_name,zipcode',
            'driver:id,name',
        ] ;
    }


    /**
     * parent id for first leg will always be 0
     *
     * @param [type] $trips_collection
     * @param [type] $key
     * @return void
     */
    public function getParentId($trips_collection, $key)
    {
        if ($key) {
            return $trips_collection[0]['id'];
        }
        return 0 ;
    }

 




    /**
     * get column names to update in recurring trips
     * i can use this in add edit trip as well $request->validated()
     * @return array
     */
    public function massAssignColumns()
    {
        return [
        "eso_id", "first_name", "middle_name", "last_name", "dob", "ssn", "member_phone_no", "master_level_of_service_id", "additional_passengers", "payor_type", "payor_id", "user_id", "group_id", "payable_type", "date_of_service", "timezone", "appointment_time", "shedule_pickup_time", "base_location_id", "pickup_address", "pickup_lat", "pickup_lng", "pickup_zip", "pickup_location_type", "pickup_facility_id", "pickup_department_name", "pickup_address_type_name", "drop_address", "drop_lat", "drop_lng", "drop_zip", "drop_location_type", "drop_facility_id", "drop_department_name", "drop_address_type_name", "estimated_trip_distance", "estimated_trip_duration", "estimated_mileage_frombase_location", "estimated_duration_frombase_location", "trip_price", "adjusted_price", "total_price", "county_type", "notes_or_instruction", "trip_format", "created_by", "long_timezone", "short_timezone", "week_day", "trip_no", "member_id", "pickup_member_address_id", "drop_member_address_id", "member_first_name", "member_middle_name", "member_last_name", "leg_no", "parent_id", "id", "recurring_master_id",
    ] ;
    }
}
