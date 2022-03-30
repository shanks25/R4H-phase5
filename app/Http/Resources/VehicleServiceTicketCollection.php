<?php

namespace App\Http\Resources;
use App\Traits\ResourceTrait;
use App\Http\Resources\VehicleServiceMasterCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VehicleServiceTicketCollection extends ResourceCollection
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
          'ticket_id' => $item->ticket_id,
          'mileage' => $item->mileage,
          'service_details' =>(new VehicleServiceMasterCollection($item->vehicleMaintenanceService)) ,
          'no_of_service'=>$item->vehicleMaintenanceService->count(),
          'request_date' => date('m-d-Y',strtotime($item->request_date)),
          'status' => $item->status,
        ];
        });
    }
    
}
