<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    public function toArray($item, $resource_for = null)
    {
        $trip = [
            'id' => $this->id,
            'driver' => $this->driver,
            'date_of_service' => modifyTripDate($this->date_of_service, $this->shedule_pickup_time),
            'shedule_pickup_time' => $this->shedule_pickup_time ? modifyTripTime($this->date_of_service, $this->shedule_pickup_time) : '',
            'appointment_time' => $this->appointment_time ?  modifyTripTime($this->date_of_service, $this->appointment_time) : '',
            'trip_no' => $this->trip_no,
            'leg_no' => $this->leg_no,
            'trip_format' => tripFormat($this->trip_format),
            'status' => $this->status,
            'payor' => $this->payor,
            'payor_type_names' => $this->payorTypeNames,
            'payor_signature' => $this->payor_signature,
            'level_of_service' => $this->levelOfService,
            'timezone' => authTimeZone(),
            'wait_time' => $this->trip_format == 4 ? 'Yes' : 'No',
            'member_phone_no' => formatPhoneNumber($this->member_phone_no),
            'trip_start_address' => formatPhoneNumber($this->trip_start_address),
            'baselocation' => $this->baselocation,
            'zone' => $this->zone ?? '',
            'additional_passengers' => $this->additional_passengers,
            'pickup_address' => $this->pickup_address,
            'pickup_lat' => $this->pickup_lat,
            'pickup_lat' => $this->pickup_lat,
            'drop_address' => $this->drop_address,
            'drop_lat' => $this->drop_lat,
            'drop_lng' => $this->drop_lng,
            'county_type' => $this->county_type == 1 ? 'Local' : 'Out of County',
            'import_file' => $this->importFile,
            'estimated_mileage_frombase_location' => decimal2digitNumber($this->estimated_mileage_frombase_location),
            'log' => $this->log,
            'estimated_trip_distance' => $this->estimated_trip_distance,
            'estimated_trip_duration' => gmdate("H:i:s", $this->estimated_trip_duration),
            'trip_price' => decimal2digitNumber($this->trip_price),
            'adjusted_price' => decimal2digitNumber($this->adjusted_price),
            'total_price' => decimal2digitNumber($this->total_price),
            'notes_or_instruction' => $this->notes_or_instruction,
            'trip_add_type' => tripAddedBy($this->trip_add_type),
            'member' => $this->member,
            'pickup_details' => $this->pickupDetails,
            'drop_details' => $this->dropDetails,
            'level_of_service' => $this->levelOfService,
            'payor_type_details' => $this->payorTypeNames,
            'parent_id' => $this->parent_id,
            'group_id' => $this->group_id,
            'importfile_id' => $this->importfile_id,
            // 'pickup_county' => $this->pickup_county,
            'county_pickup_names' => $this->countyPickupNames,
            'county_drop_names' => $this->countyDropNames,



        ];
        /* in edit trip we show time based on selected timezone */
        if (isset($this->is_it_for_edit)) {
            $trip['date_of_service'] = modifyTripTimeForEditTrip($this->date_of_service, $this->appointment_time, $this->timezone)->format('Y-m-d');
            $trip['shedule_pickup_time'] = modifyTripTimeForEditTrip($this->date_of_service, $this->shedule_pickup_time, $this->timezone)->format('H:i');
            $trip['appointment_time'] = modifyTripTimeForEditTrip($this->date_of_service, $this->appointment_time, $this->timezone)->format('H:i');
            $trip['timezone'] = $this->timezone;
            $trip['i_can_add_leg'] = $this->i_can_add_leg;
        }


        return $trip;
    }
    public function with($request)
    {
        return  metaData(true, $request, '20002', 'success', 200, '');
    }
}
