<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ImportNamesCollection extends ResourceCollection
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
                'logo' => asset($item->logo)
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
        $metaData =  metaData(true, $request, '2012', 'success', 200, '');
        return  merge($metaData, $data);
    }
}
