<?php

namespace App\Http\Resources;

use App\Traits\ResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CityCollection extends ResourceCollection
{
  

   
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
  
  
        $metaData =  metaData(true, $request, '3012', 'success', 200, '');
        return  merge($metaData, $data);
   
    }
  
  
    public function map($collection)
    {
      return $collection->map(function ($item) {
        
        return [
                'id' => $item->id,
                'name' => $item->city,
                
           ];
         });
    }
  
 
}
