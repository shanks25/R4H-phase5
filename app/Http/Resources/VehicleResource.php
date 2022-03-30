<?php

namespace App\Http\Resources;
use App\Models\Vehicle;
use Illuminate\Http\Resources\Json\JsonResource;
class VehicleResource extends JsonResource
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
                'type' => $this->type,
                'year' => $this->Year,
                'modelNo' => $this->model_no,
                'vin' => $this->VIN,
                'unitNo' => $this->unit_no,
                'cstNo' => $this->CTS_no,
                'registrationExpiryDate' => date('m-d-Y',strtotime($this->registration_expiry_date)),
                'insuranceExpiryDate' => date('m-d-Y',strtotime($this->insurance_expiry_date)),
                'licensePlate' => $this->license_plate,
                'milesPerGallon' => $this->miles_per_gallon,
                'document' => url($this->documents),
                'status' => $this->status,
                'odometerStartDate' => date('m-d-Y',strtotime($this->odometer_start_date)),
                'odometerOnStartDate' => Vehicle::getOdometer($this->id),
                'lavelOFsevice' =>(new LevelofServiceCollection($this->masterLevelservices)) ,
        ];
    }
}
