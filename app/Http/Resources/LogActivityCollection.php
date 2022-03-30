<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LogActivityCollection extends ResourceCollection
{
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


        $metaData = metaData(true, $request, '2013');
        return  merge($metaData, $data);
    }

    public function map($collection)
    {
        return $collection->map(function ($item) {

            return [
                'id' => $item->id,
                'trip_id' => $item->trip_id,
                'subject' => $item->subject,
                'url' => $item->url,
                'method' => $item->method,
                'ip' => $item->ip,
                'agent' => $item->agent,
                'request' => $item->request,
                'created_at' => modifyTripWithDateTime($item->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
}
