<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayorLogResource extends JsonResource
{
    public function toArray($request)
    {
        $trip = [
            'id' => $request->id,
            'driver' => $request->driver,
            'date_of_service' => modifyTripDate($request->date_of_service, $request->shedule_pickup_time),
            'shedule_pickup_time' => modifyTripTime($request->date_of_service, $request->shedule_pickup_time),
            'vehicle' => $request->vehicle,
            'trip_no' => $request->trip_no,
            'status' => $request->status,
            'payor' => $request->payor,
            'payor_type_names' => $request->payorTypeNames,
            'payor_signature' => $request->payor_signature,
            'level_of_service' => $request->levelOfService ?? '',
            'timezone' => authTimeZone(),
            'appointment_time' => modifyTripTime($request->date_of_service, $request->appointment_time),
            'pickup_address' => $request->pickup_address,
            'drop_address' => $request->drop_address,
            'log' => $request->log,
            'total_unloaded_mileage' => $request->log->period2_miles ?? '',
            'total_loaded_mileage' => $request->log->period3_miles ?? '',
            'total_price' => decimal2digitNumber($request->log->total_trip),
            'log_status' => $request->log->log_status,

        ];

        return $trip;
    }
    public function with($request)
    {
        return  metaData(true, $request, '4013', 'success', 200, '');
    }
}
