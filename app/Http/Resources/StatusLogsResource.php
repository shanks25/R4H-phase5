<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatusLogsResource extends JsonResource
{
    public function toArray($item)
    {
        return [
            'id' => $this->id,
            'driver_id' => $this->driver_id,
            'date_of_service' => modifyTripDate($this->date_of_service, $this->shedule_pickup_time),
            'shedule_pickup_time' => modifyTripTime($this->date_of_service, $this->shedule_pickup_time),
            'trip_no' => $this->trip_no,
            'last_remitted_cost' => $this->last_remitted_cost,
            // 'invoice_exist' => $this->invoice_exist,
            'trip_price' => $this->trip_price,
            'adjusted_price' => $this->adjusted_price,
            'total_price' => $this->total_price,
            'status_id' => $this->status_id,
            'vehicle_id' => $this->vehicle_id,
            'timezone' => eso()->timezone,
            'status_logs' => $this->status_logs,
            'log' => $this->log,
        ];
    }
    public function with($request)
    {
        return  metaData(true, $request, '2010', 'success', 200, '');
    }
}
