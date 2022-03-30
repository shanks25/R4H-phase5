<?php

namespace App\Http\Resources;

use App\Traits\ResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CountyCollection extends ResourceCollection
{
  use ResourceTrait;

  public function with($request)
  {
      $data = [
          'meta' =>[
                    'total' => $this->collection->count()
               ], 
        ];

        
        $metaData = metaData(true,$request,'3015','success');
       return  merge($metaData,$data);  

  }
 
}
