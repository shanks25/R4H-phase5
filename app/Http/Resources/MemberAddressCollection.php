<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MemberAddressCollection extends ResourceCollection
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
                'member_id' => $item->member_id,
                'address_name' => $item->address_name,
                'location_type' => $item->location_type == 1 ? 'Non Facility' : 'Facility',
                'street_address' => $item->street_address,
                'zipcode' => $item->zipcode,
                'latitude' => $item->latitude,
                'longitude' => $item->longitude,
                'facility' => $item->facility ?? '',

            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '20001', 'success', 200, '');
    }
}
