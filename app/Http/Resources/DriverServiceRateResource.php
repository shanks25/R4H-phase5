<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverServiceRateResource extends JsonResource
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
            'driver_id' => $this->driver_id,
            'level_of_service_id' => $this->level_of_service_id,
            'unloaded_rate_per_mile' => $this->unloaded_rate_per_mile,
            'loaded_rate_per_mile'=>$this->loaded_rate_per_mile,
            'loaded_rate_per_mile_base'=>$this->loaded_rate_per_mile_base,
            'unloaded_rate_per_hr'=>$this->unloaded_rate_per_hr,
            'loaded_rate_per_hr'=>$this->loaded_rate_per_hr,
            'flat_rate'=>$this->flat_rate,
            'base_rate'=>$this->base_rate,
            'wait_time_per_hour'=>$this->wait_time_per_hour,
            'minimum_payout'=>$this->minimum_payout,
            'unloaded_rate_per_mile_out'=>$this->unloaded_rate_per_mile_out,
            'loaded_rate_per_mile_out'=>$this->loaded_rate_per_mile_out,
            'unloaded_rate_per_hr_out'=>$this->unloaded_rate_per_hr_out,
            'loaded_rate_per_hr_out'=>$this->loaded_rate_per_hr_out,
            'insurance_rate_per_mile_out'=>$this->insurance_rate_per_mile_out,
            'base_rate_out'=>$this->base_rate_out,
            'wait_time_per_hour_out'=>$this->wait_time_per_hour_out,
            'minimum_payout_out'=>$this->minimum_payout_out,
        ];
    }
}
