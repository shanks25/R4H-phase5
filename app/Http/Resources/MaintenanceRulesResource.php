<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VehicleServiceMasterCollection;

class MaintenanceRulesResource extends JsonResource
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
            'name' => $this->name,
            'vehicle_id' => $this->vehicle_id,
            'servicing_miles' => $this->servicing_miles,
            'modelNo' => !empty($this->vehicle->model_no)?$this->vehicle->model_no:'',
            'notification_content' => $this->notification_content,
            'vin' => !empty($this->vehicle->VIN)?$this->vehicle->VIN:'',
            'requested_date' => date('m-d-Y',strtotime($this->created_at)),
            'service' => (new VehicleServiceMasterCollection($this->vehicleRuleService))
        ];
    }
}
