<?php

namespace App\Http\Resources;

use App\Http\Resources\DriverLeavesCollection;
use App\Http\Resources\LevelofServiceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverViewResource extends JsonResource
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
            'driver_type' => $this->driver_type,
            'insurance_type' => $this->insurance_type,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'DOB' => date('m-d-Y',strtotime($this->DOB)),
            'mobile_no' => $this->mobile_no,
            'license_no' => $this->license_no,
            'license_state' => $this->license_state,
            'license_expiry' => date('m-d-Y',strtotime($this->license_expiry)),
            'upload_license' =>url($this->upload_license),
            'email' => $this->email,
            'status' => $this->status,
            'address' => $this->address,
            'password' => $this->password,
            'timezone' => $this->timezone,
            'signed_image' => url($this->upload_signature_original),
            'employee_id' => $this->employee_id,
            'position' => $this->position,
            'department_code' => $this->department_code,
            'driving_experience' => $this->driving_experience,
            'work_start_date' => date('m-d-Y',strtotime($this->work_start_date)),
            'hire_date' => date('m-d-Y',strtotime($this->hire_date)),
            'work_status' => $this->work_status,
            'insurance_status' => $this->insurance_status,
            'insurance_id' => $this->insurance_id,
            'work_timing' => $this->work_timing,
            'zone' => $this->zone,
            'allow_vehicle_home' => $this->allow_vehicle_home,
            'stretcher' => $this->stretcher,
            'can_overright' => $this->can_overright,
            'fav_passenger' => $this->fav_passenger,
            'paralift' => $this->paralift,
            'weight_limitation' => $this->weight_limitation,
            'assistant' => $this->assistant,
            'training_video' => $this->training_video,
            'allowed_hours' =>  $this->allowed_hours,
            'ability_get_trips' => $this->ability_get_trips,
            'attendant' =>  $this->attendant,
            'central_registry' =>  $this->central_registry,
            'fingerprint' =>  $this->fingerprint,
            'enabled_cancel' =>  $this->enabled_cancel,
            'route_sequence' =>  $this->route_sequence,
            'decline_orders' =>  $this->decline_orders,
            'bbp' =>  $this->bbp,
            'leaves' => (new DriverLeavesCollection($this->driverLeaveDetails)) ,
            'rates' => $this->driverServiceRate,
            'vehicle' => $this->vehicle,
            'levelOFsevice' =>(new LevelofServiceCollection($this->driverLevelservices)),
            
            
        ];
    }
}
