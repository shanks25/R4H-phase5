<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MemberListCollection extends ResourceCollection
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

    $function_code = $request->path() == 'members' ? '1001' : '1002'; // 1002 is for search member api
    $metaData = metaData(true,$request, $function_code,'success','','','');
    return  merge($metaData, $data);
  }

  public function map($collection)
  {
    return $collection->map(function ($item) {
      
      return [
              'id' => $item->id,
              'member_since' => date('m-d-Y',strtotime($item->created_at)),
              'member_name' => $item->name,
              'trips' => count($item->trips),
              'address' => $item->address,
              'primary_level_of_service' => $item->masterLevelOfService,
              'date_of_birth' => $item->dob,
              'mobile_no' => formatPhoneNumber($item->mobile_no),
                           
         ];
       });
  }
}
