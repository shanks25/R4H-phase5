<?php

namespace App\Http\Resources;

use App\Traits\ResourceTrait;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MemberCollection extends ResourceCollection
{
    use ResourceTrait;
    public function with($request)
    {
        $data = [
      'meta' => [
        'total' => $this->collection->count()
      ],
    ];

        $function_code = $request->path() == 'members' ? '1001' : '1002'; // 1002 is for search member api
        $metaData = metaData(true, $request, $function_code, 'success', '', '', '');
        return  merge($metaData, $data);
    }

    public function map($collection)
    {
        return $collection->map(function ($item) {
            return [
              'id' => $item->id,
              'name' => $item->name,
              'first_name' => $item->first_name,
              'middle_name' => $item->middle_name ?? '',
              'last_name' => $item->last_name,
              'ssn' => $item->ssn ?? '',
              'dob' => $item->dob ?? '',
              'mobile_no' => $item->mobile_no ?? '',
          ];
        });
    }
}
