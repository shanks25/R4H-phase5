<?php

namespace App\Http\Resources;
use App\Models\Vehicle;
use App\Traits\ResourceTrait;
use App\Http\Resources\LevelofServiceCollection;
use App\Http\Resources\VehicleServiceMasterCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MaintenanceRulesCollection extends ResourceCollection
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
  
  
        $metaData =  metaData(true, $request, '30040', 'success', 200, '');
        return  merge($metaData, $data);
   
    }


    public function map($collection)
    {
      return $collection->map(function ($item) {
        
        return [
                'id' => $item->id,
                'vehicle_id' => $item->vehicle_id,
                'servicing_miles' => $item->servicing_miles,
                'modelNo' => !empty($item->vehicle->model_no)?$item->vehicle->model_no:'',
                'notification_content' => $item->notification_content,
                'vin' => !empty($item->vehicle->VIN)?$item->vehicle->VIN:'',
                'requested_date' => date('m-d-Y',strtotime($item->created_at)),
                'service' => (new VehicleServiceMasterCollection($item->vehicleRuleService)),
           ];
         });
    }
}
