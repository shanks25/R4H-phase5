<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TripRecurringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'weekdays' => $this->weekdays,
            'days' =>  $this->days,
            'start_date' => $this->start_date,
            'end_date' =>  $this->end_date,
            'trip_count' =>  $this->trip_count,
            'created_at' =>  modifyTripDate($this->created_at),
            'trips' =>  $this->trips,
        ];
    }
}
