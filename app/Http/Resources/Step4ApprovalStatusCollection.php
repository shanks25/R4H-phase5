<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Step4ApprovalResource;

class Step4ApprovalStatusCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public $collects = Step4ApprovalResource::class;

    
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
  
  
        $metaData =  metaData(true, $request, '5012', 'success', 512, '');
        return  merge($metaData, $data);
   
    }
}
