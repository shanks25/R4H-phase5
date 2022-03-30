<?php

namespace App\Http\Resources;

use App\Traits\ResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseLocationCollection extends ResourceCollection
{
  public function toArray($request)
  {
    return [
      'data' => $this->map($this->collection),
    ];
  }



  public function map($collection)
  {
    return $collection->map(function ($item) {

      return [
        'id' => $item->id,
        'name' => $item->name,
        'address' => $item->address,
      ];
    });
  }

  public function with($request)
  {
    $data = [
      'meta' => [
        'total' => $this->collection->count()
      ],
    ];

    $metaData = metaData(true, $request, '1007', 'success', '', '', '');
    return  merge($metaData, $data);
  }
}
