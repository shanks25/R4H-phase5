<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PayoutCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->map($this->collection),
        ];
    }

    public function with($request)
    {
        $data = [
            'meta' => [
                'total' => $this->collection->count()
            ],
        ];


        $metaData = metaData(true, $request, '20004');
        return  merge($metaData, $data);
    }

    public function map($collection)
    {
        return $collection->map(function ($item) {

            return [
                'id' => $item->id,
                'name' => $item->name,
            ];
        });
    }
}
