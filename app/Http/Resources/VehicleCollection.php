<?php

namespace App\Http\Resources;
use App\Models\Vehicle;
use App\Traits\ResourceTrait;
use App\Http\Resources\LevelofServiceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VehicleCollection extends ResourceCollection
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
  
  
        $metaData =  metaData(true, $request, '20001', 'success', 200, '');
        return  merge($metaData, $data);
   
    }


    public function map($collection)
    {
      return $collection->map(function ($item) {
        
        return [
                'id' => $item->id,
                'type' => $item->type,
                'year' => $item->Year,
                'modelNo' => $item->model_no,
                'vin' => $item->VIN,
                'unitNo' => $item->unit_no,
                'cstNo' => $item->CTS_no,
                'registrationExpiryDate' => date('m-d-Y',strtotime($item->registration_expiry_date)),
                'insuranceExpiryDate' => date('m-d-Y',strtotime($item->insurance_expiry_date)),
                'licensePlate' => $item->license_plate,
                'status' => $item->status,
                'odometerOnStartDate' =>Vehicle::getOdometer($item->id),
                'odometerStartDate' => date('m-d-Y',strtotime($item->odometer_start_date)),
                'levelOFsevice' =>(new LevelofServiceCollection($item->masterLevelservices)),
                
           ];
         });
    }
}
