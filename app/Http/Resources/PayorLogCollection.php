<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\StatusLogTimeCollection;

class PayorLogCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->map($this->collection),
        ];
    }

    public function map($collection)
    {
        return $collection->map(function ($item) {
            return [
                'id' => $item->id,
                'driver' => $item->driver ?? '',
                'date_of_service' => modifyTripDate($item->date_of_service, $item->shedule_pickup_time),
                'shedule_pickup_time' => modifyTripTime($item->date_of_service, $item->shedule_pickup_time),
                'vehicle' => $item->vehicle,
                'trip_no' => $item->trip_no,
                'status' => $item->status,
                'payor' => $item->payor,
                'member' => $item->member,
                'payor_type_names' => $item->payorTypeNames,
                'payor_signature' => $item->payor_signature,
                'level_of_service' => $item->levelOfService ?? '',
                'timezone' => authTimeZone(),
                'appointment_time' => modifyTripTime($item->date_of_service, $item->appointment_time),
                'pickup_address' => $item->pickup_address,
                'drop_address' => $item->drop_address,
                'log' => $item->log,
                'trip_time' => (new StatusLogTimeCollection($item->statusLogs)),
                'total_unloaded_mileage' => $item->log->period2_miles ?? '',
                'total_loaded_mileage' => $item->log->period3_miles ?? '',
                'total_price' => decimal2digitNumber($item->total_price),
                'trip_status' => $item->status->status_description,

            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4013', 'success', 200, '');
    }
}
