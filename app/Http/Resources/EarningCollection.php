<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EarningCollection extends ResourceCollection
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
                'date' => date('m/d/Y', strtotime($item->date)),
                'day' => date('l', strtotime($item->date)),
                'total_trips' => $item->total_trips ?? 0,
                'total_driver' => $item->total_driver ?? 0,
                'total_vehicle' => $item->total_vehicle ?? 0,
                'total_level_of_service' => $item->total_level_of_service ?? 0,
                'zipcodes' => $item->total_pickup_zip ?? 0,
                'total_revenue' => $item->total_revenue ?? 0,
                'total_profit_percent' => profitMargin($item->trip_profit,$item->total_revenue),
                'total_trips_mileage' =>$item->total_miles ?? 0,
                'total_profit' => number_format($item->trip_profit,2) ?? 0,
                'driver_payout' => number_format($item->driver_pay, 2) ?? 0,
                'total_duration' => $item->total_duration,
            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4037', 'success', 200, '');
    }
}
