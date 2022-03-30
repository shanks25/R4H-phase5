<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OnboardCollection extends ResourceCollection
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
                'trip_no' => $item->trip_no,
                'date_of_service' => modifyTripDate($item->date_of_service, $item->shedule_pickup_time),
                'shedule_pickup_time' => modifyTripTime($item->date_of_service, $item->shedule_pickup_time),
                'pickup_address' => $item->pickup_address,
                'drop_address' => $item->drop_address,
                'member_name' => $item->Member_name,
                'member' => $item->member,
                'level_of_service' => $item->levelOfService,
                'payor_type_names' => $item->payorTypeNames,
                'payor' => $item->payor,
                'pickup_zip' => $item->pickup_zip,
                'drop_zip' => $item->drop_zip,
                'county_type' => $item->county_type == 1 ? 'Local' : 'Out of County',

                'trip_price' => $item->trip_price,
                'adjusted_price' => $item->adjusted_price,
                'total_price' => $item->total_price,

            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '2015', 'success', 200, '');
    }
}
