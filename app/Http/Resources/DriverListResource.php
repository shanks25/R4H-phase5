<?php

namespace App\Http\Resources;
use Carbon\Carbon;
use App\Http\Resources\LevelofServiceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverListResource extends JsonResource
{
     /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $reg_days = now()->diffInDays(Carbon::parse($this->license_expiry));
        if ($reg_days <= 30 && $reg_days > 0) {
        $rowclass = "expiresoon";
        } elseif ($reg_days < 1) {
        $rowclass = "expired";
        }else{
          $rowclass = "active";
        }

      return [
        'id' => $this->id,
        'driver_type' => $this->driver_type,
        'insurance_type' => $this->insurance_type,
        'name' => $this->name,
        'DOB' => date('d-m-Y',strtotime($this->DOB)),
        'mobile_no' => $this->mobile_no,
        'license_no' => $this->license_no,
        'license_state' => $this->license_state,
        'license_expiry' => date('m-d-Y',strtotime($this->license_expiry)),
        'expiry_flag' => $rowclass,
        'upload_license' =>url($this->upload_license),
        'email' => $this->email,
        'status' => $this->status,
        'password' => $this->password,
        'timezone' => $this->timezon,
        'signed_image' => url($this->upload_signature_original),
        'vehicle' => $this->vehicle,
        'levelOFsevice' =>(new LevelofServiceCollection($this->driverLevelservices)),
        
       ];
    }
 
}
