<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DriverUtiliztionCollection extends ResourceCollection
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
                'driver' => $item->name,
                'driver_type' => $item->driver_type,
                'insurance_type' => $item->insurance_type,
                'date' => date('m/d/Y', strtotime($item->date)),
                'vehicle' => $item->VIN,
                'in_time' => modifyTripTime($item->utilization_date, date("g:i A", strtotime($item->in_time))),
                'out_time' => modifyTripTime($item->utilization_date, date("g:i A", strtotime($item->out_time))),
                'total_clocked_hrs' => $item->total_driver_clock_duration ?? 0,
                'total_driver_hrs' => $item->total_duration ?? 0,
                'total_downtime_hrs' => $item->downtime_duration ?? 0,
                'total_p2_hrs' => $item->total_p2_duration ?? 0,
                'total_p3_hrs' => $item->total_p3_duration ?? 0,
                'total_period2_miles' => $item->total_period2_miles ?? 0,
                'total_period3_miles' => $item->total_period3_miles ?? 0,
                'total_trips' => $item->total_trips ?? 0,
                'total_trip_cost' => number_format($item->total_trip_cost, 2) ?? 0,
                'total_trips' => number_format($item->total_insurance_cost, 2) ?? 0,
                'driver_payout' => number_format($item->driver_pay, 2) ?? 0,
                'trip_profit' => number_format($item->trip_profit,2) ?? 0,
                'driver_utiliztion_s' => decimal2digit_number($item->total_trip_cost / 500) ?? 0,
                'driver_utiliztion_hrs' => decimal2digit_number(( $item->total_period2_miles + $item->total_period3_miles ) / 8) ?? 0,
            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4037', 'success', 200, '');
    }
}
