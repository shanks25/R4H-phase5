<?php

namespace App\Http\Resources;
use App\Traits\ResourceTrait;
use App\Http\Resources\CrmResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CrmCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public $collects = CrmResource::class;

    
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
  
  
        $metaData =  metaData(true, $request, '4001', 'success', 200, '');
        return  merge($metaData, $data);
   
    }

    
}
