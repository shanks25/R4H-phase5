<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\StatusLogTimeCollection;

class TripPaymentHistoryCollection extends ResourceCollection
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
                'driver' => $item->driver,
                'date_of_service' => modifyTripDate($item->date_of_service, $item->shedule_pickup_time),
                'shedule_pickup_time' => modifyTripTime($item->date_of_service, $item->shedule_pickup_time),
                'vehicle' => $item->vehicle,
                'trip_no' => $item->trip_no,
                'status' => $item->status,
                'payor' => $item->payor,
                'payor_type_names' => $item->payorTypeNames,
                'payor_signature' => $item->payor_signature,
                'level_of_service' => $item->levelOfService ?? '',
                'timezone' => authTimeZone(),
                'trip_start_address' => formatPhoneNumber($item->trip_start_address),
                'baselocation' => $item->baselocation,
                'zone' => $item->zone ?? '',
                'pickup_address' => $item->pickup_address,
                'drop_address' => $item->drop_address,
                'county_type' => $item->county_type == 1 ? 'Local' : 'Out of County',
                'estimated_mileage_frombase_location' => decimal2digitNumber($item->estimated_mileage_frombase_location),
                'log' => $item->log,
                'trip_time' => (new StatusLogTimeCollection($item->statusLogs)),
                'estimated_trip_distance' => $item->estimated_trip_distance,
                'estimated_trip_duration' => gmdate("H:i:s", $item->estimated_trip_duration),
                'trip_price' => decimal2digitNumber($item->trip_price),
                'adjusted_price' => decimal2digitNumber($item->adjusted_price),
                'total_price' => decimal2digitNumber($item->total_price),
                'notes_or_instruction' => $item->notes_or_instruction,
                'trip_added_by' => tripAddedBy($item->trip_add_type),
                'trip_added_by_url' => $item->importFile ? $item->importFile->imported_file : '',
                'trip_added_by' => tripAddedBy($item->trip_add_type),

            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4033', 'success', 200, '');
    }
}
