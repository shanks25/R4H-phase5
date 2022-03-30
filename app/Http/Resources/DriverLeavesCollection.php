<?php

namespace App\Http\Resources;
use App\Traits\ResourceTrait;
use App\Http\Resources\LevelofServiceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DriverLeavesCollection extends ResourceCollection
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



    public function map($collection)
    {
      return $collection->map(function ($item) {
        
        return [
                'id' => $item->id,
                'driver_id' => $item->driver_id,
                'start_date' => date('m-d-Y H:i:s',strtotime($item->start_date)),
                'end_date' => date('m-d-Y H:i:s',strtotime($item->end_date)),
                'status' => $item->status,
                'resume_date' => date('m-d-Y H:i:s',strtotime($item->resume_date)),
                'reason' => $item->reason,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
              
           ];
         });
    }
}
