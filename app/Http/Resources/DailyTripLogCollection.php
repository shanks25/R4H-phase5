<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DailyTripLogCollection extends ResourceCollection
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
                'date_of_service' => date('m/d/Y', strtotime($item->date)),
                'total_no_of_trips' => $item->trip_count,
                'total_no_of_vehicles' => $item->vehicles + $item->other_drivers,
                'total_no_of_drivers' => $item->drivers + $item->other_drivers,
                'total_member' =>$item->total_additional_passengers + $item->members,
                'total_trip_duration' => gmdate("H:i:s", $item->time),
                'total_trip_miles' => $item->total_miles ?? 0,
                'total_trip_cost' => number_format($item->total_trip_price, 2) ?? 0,
                'driver_payout' => number_format($item->driver_payout, 2) ?? 0,
                'total_insurace' => round($item->total_insurace,2) ?? 0,
                'total_profit' => number_format($item->trip_profit,2) ?? 0,
                'accident' => accidentCount($item->date),
            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4037', 'success', 200, '');
    }
}
