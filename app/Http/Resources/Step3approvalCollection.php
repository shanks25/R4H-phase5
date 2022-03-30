<?php

namespace App\Http\Resources;
use App\Http\Resources\Step3approvalResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class Step3approvalCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public $collects = Step3approvalResource::class;

    
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
  
  
        $metaData =  metaData(true, $request, '5010', 'success', 510, '');
        return  merge($metaData, $data);
   
    }
}
