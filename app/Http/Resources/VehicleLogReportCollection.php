<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VehicleLogReportCollection extends ResourceCollection
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
                'date' => $item->date,
                'model_no' => $item->model_no,
                'VIN' => $item->VIN,
                'name' => $item->name,
                'odometer' => $item->reading ?? 0,
                'total_miles' => $item->total_miles ?? 0,
                'trip_count' => $item->trip_count ?? 0,
                'time' => gmdate("H:i:s", $item->time),
                'total_trip_price' => $item->total_trip_price ?? 0,
                'Vehicle_utilization_$' => $item->total_trip_price / 500 ?? 0,
                'Vehicle_utilization_5' => $item->total_time / 8 ?? 0,

            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4044', 'success', 200, '');
    }
}
