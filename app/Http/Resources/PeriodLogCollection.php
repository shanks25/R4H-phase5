<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PeriodLogCollection extends ResourceCollection
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
                // 'fairmetic_drive_id' => $item->driver ? $item->driver->id: '',
                'vehicle' => $item->vehicle,
                'log' => $item->log,
                'trips_completed' => $item->driver ? tripsCompleted($item->driver->id) : '',
                'payout_type' => $item->payout_type ?? '',
                'period2' =>$item->log->period2 ?? '',
                'period3' =>  $item->log->period3 ?? '',
                'total_miles_driven_for_period2' => $item->log ? $item->log->period2_miles: '',
                'total_miles_driven_for_period3' => $item->log ? $item->log->period3_miles: '',

            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4016', 'success', 200, '');
    }
}
