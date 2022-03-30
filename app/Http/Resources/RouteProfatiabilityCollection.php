<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\StatusLogTimeCollection;

class RouteProfatiabilityCollection extends ResourceCollection
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
                'date' => $item->date,
                'start_zip' => $item->pickup_zip,
                'end_zip' => $item->drop_zip,
                'totalTrips' => $item->total_trips,
                'total_drive_duration' => $item->total_duration,
                'total_trip_cost' => number_format($item->total_trip_cost, 2),
                'driver_pay' => $item->driver_pay ?? 0,
                'total_profit' => $item->trip_profit ?? 0,
                'profit_margin' => $item->total_trip_profit ? profitMargin($item->total_trip_profit,$item->total_trip_cost):'',

            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4036', 'success', 200, '');
    }
}
