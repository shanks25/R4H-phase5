<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\StatusLogTimeCollection;

class DriverLogCollection extends ResourceCollection
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
                'log' => $item->log,
                'payor_type_names' => $item->payorTypeNames,
                'payor_signature' => $item->payor_signature,
                'level_of_service' => $item->levelOfService ?? '',
                'pickup_address' => $item->pickup_address,
                'drop_address' => $item->drop_address,
                'trip_time' => (new StatusLogTimeCollection($item->statusLogs)),
                'trip_duration' => $item->log ? secondToTimes($item->log->estimated_trip_duration): '',
                'wait_time' => $item->trip_format == 4 ? 'Yes' : 'No',
                'wait_tme_duration' => $item->log ? secondToTimes($item->log->wait_time_sec): '',
                'total_drive_duration' =>  '',
                'trip_status' => $item->status->status_description,

            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4015', 'success', 200, '');
    }
}
