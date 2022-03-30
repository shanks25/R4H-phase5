<?php

namespace App\Http\Resources;

use App\Traits\ResourceTrait;
use App\Http\Resources\BaseLocationResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseLocationListCollection extends ResourceCollection
{
  public $collects = BaseLocationResource::class;

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


      $metaData =  metaData(true, $request, '3016', 'success', 200, '');
      return  merge($metaData, $data);
 
  }

 
  public function map($collection)
  {
    return $collection->map(function ($item) {
      
      return [
        'id' => $item->id,
        'city_name' => $item->name,
        'city_id' => $item->city_id,
        'state' => $item->state,
        'address' => $item->address,
        'zipcode' =>  $item->zipcode,
        'default_location' => $item->is_default_location,
        'created_at' => date('m-d-Y',strtotime($item->created_at))
      
        ];
      });
  }

}
