<?php

namespace App\Http\Resources;
use App\Traits\ResourceTrait;
use App\Http\Resources\LevelofServiceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;
class DriverListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
   
    public function toArray($request)
    {
        return [
               'data'=>$this->map($this->collection) ,
        ]; 
    }


    public function with($request)
    {
      $data = [
        'meta' => [
          'total' => $this->collection->count()
        ],
      ];
  
  
        $metaData =  metaData(true, $request, '30026', 'success', 200, '');
        return  merge($metaData, $data);
   
    }


    public function map($collection)
    {
      return $collection->map(function ($item) {

         $reg_days = now()->diffInDays(Carbon::parse($item->license_expiry));
            if ($reg_days <= 30 && $reg_days > 0) {
            $rowclass = "expiresoon";
            } elseif ($reg_days < 1) {
            $rowclass = "expired";
            }else{
              $rowclass = "active";
            }

        return [
                'id' => $item->id,
                'driver_type' => $item->driver_type,
                'insurance_type' => $item->insurance_type,
                'name' => $item->name,
                'DOB' => date('d-m-Y',strtotime($item->DOB)),
                'mobile_no' => $item->mobile_no,
                'license_no' => $item->license_no,
                'license_state' => $item->license_state,
                'license_expiry' => date('m-d-Y',strtotime($item->license_expiry)),
                'expiry_flag' => $rowclass,
                'upload_license' =>url($item->upload_license),
                'email' => $item->email,
                'status' => $item->status,
                'password' => $item->password,
                'timezone' => $item->timezon,
                'signed_image' => url($item->upload_signature_original),
                'vehicle' => $item->vehicle,
                'levelOFsevice' =>(new LevelofServiceCollection($item->driverLevelservices)),
                
           ];
         });
    }
}
