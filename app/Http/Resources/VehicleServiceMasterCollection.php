<?php

namespace App\Http\Resources;

use App\Traits\ResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VehicleServiceMasterCollection extends ResourceCollection
{
  use ResourceTrait;

    public function with($request)
    {
        $data = [
            'meta' =>[
                      'total' => $this->collection->count()
                 ], 
          ];

          
          $metaData = metaData(true,$request,'10004','success');
         return  merge($metaData,$data);  

    }

 
}
