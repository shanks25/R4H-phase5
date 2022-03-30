<?php

namespace App\Http\Resources;
use App\Traits\ResourceTrait;
use App\Http\Resources\LevelofServiceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AccidentCollection extends ResourceCollection
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
                'date_of_accident' => $item->date,
                'owner'=> $item->user_id,
                'vehicle_id'=> $item->vehicle_id,
                'year'=> $item->year,
                'make'=> $item->other_vehicle_make,
                'model'=> $item->other_vehicle_model,
                'driver'=> $item->driver_id,
                'status'=> $item->status_id,
                'logged_date'=> $item->created_at,
                
           ];
         });
    }

   
}
