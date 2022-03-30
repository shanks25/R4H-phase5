<?php

namespace App\Http\Resources;
use App\Http\Resources\PayorInfoResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PayorInfoCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public $collects = PayorInfoResource::class;

    
    public function toArray($request)
    {
        return [
               'data'=>$this->collection ,
        ]; 
    }
    public function with($request)
    {
      $data = [
        'meta' => [
          'total' => $this->collection->count()
        ],
      ];
  

        return  $data;
   
    }

    
}
