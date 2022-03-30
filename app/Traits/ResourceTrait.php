<?php

namespace App\Traits;

trait ResourceTrait
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
            ];
        });
    }
}
