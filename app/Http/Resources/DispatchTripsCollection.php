<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DispatchTripsCollection extends ResourceCollection
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
                'date_of_service' => modifyTripDate($item->date_of_service, $item->shedule_pickup_time),
                'shedule_pickup_time' => modifyTripTime($item->date_of_service, $item->shedule_pickup_time),
                'trip_no' => $item->trip_no,
                'leg_no' => $item->leg_no,
                'member_name' => $item->member_name,
                'member_phone_no' => $item->member_phone_no,
                'trip_format' => tripFormat($item->trip_format),
                'level_of_service' => $item->levelOfService,
                'pickup_address' => $item->pickup_address,
                'drop_address' => $item->drop_address,
                'estimated_trip_distance' => $item->estimated_trip_distance,
                'estimated_trip_duration' => gmdate("H:i:s", $item->estimated_trip_duration),
                'total_price' => $item->total_price,
                'pickup_zip' => $item->pickup_zip,
                'drop_zip' => $item->drop_zip,
            ];
        });
    }

    public function with($request)
    {
        $data = [
            'meta' => [
                'total' => $this->collection->count()
            ],
        ];
        $metaData = metaData(true, $request, '2017', 'success', 200, ''); //metaData(true, $request, '20003');
        return  merge($metaData, $data);
    }
}
