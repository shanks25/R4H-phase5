<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class GarageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public $collects = GarageResource::class;
    
    
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }


    public function with($request)
    {
        $data = [
            'meta' => [
                'total' => $this->collection->count()
            ],
        ];

        $metaData =  metaData(true, $request, '20001', 'success', 200, '');
        return  merge($metaData, $data);
    }


    // public function map($collection)
    // {
    //     return $collection->map(function ($item) {

    //         return [
    //             'id' => $item->id,
    //             'name' => $item->name,
    //             'email' => $item->email,
    //             'eso_id' => $item->user_id,
    //             // 'created_at' => $item->created_at,
    //             // 'updated_at' => $item->updated_at,
    //         ];
    //     });
    // }
}
