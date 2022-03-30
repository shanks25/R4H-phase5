<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\StatusLogTimeCollection;

class BillingTripCollection extends ResourceCollection
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
                'date_of_service' => modifyTripDate($item->date_of_service, $item->shedule_pickup_time),
                'member' => $item->member,
                'shedule_pickup_time' => modifyTripTime($item->date_of_service, $item->shedule_pickup_time),
                'vehicle' => $item->vehicle,
                'trip_id' => $item->trip_no,
                'status' => $item->status,
                'payor' => $item->payor,
                'payor_type_names' => $item->payorTypeNames,
                'payor_signature' => $item->payor_signature,
                'level_of_service' => $item->levelOfService ?? '',
                'driver' => $item->driver,
                'trip_start_address' => formatPhoneNumber($item->trip_start_address),
                'pickup_address' => $item->pickup_address,
                'drop_address' => $item->drop_address,
                'county_type' => $item->county_type == 1 ? 'Local' : 'Out of County',
                'log' => $item->log,
                'total_unloaded_miles' => $item->log ? round($item->log->period2_miles,4): '',
                'total_loaded_miles' => $item->log ? round($item->log->period3_miles,4) : '',
                'trip_time' => (new StatusLogTimeCollection($item->statusLogs)),
                'unloaded_times' =>$item->log ? $item->log->unloaded_minutes : '',
                'loaded_times' => $item->log ? $item->log->loaded_minutes : '',
                'wait_time' => $item->log ? secondToTimes($item->log->wait_time_sec) : '',
                'trip_price' => $item->log ? decimal2digitNumber($item->log->total_price):'',
                'previous_payment_detail' => '-',
                'inv_amount' => $item->log ? decimal2digitNumber($item->log->total_price):'',
                'trip_status' => $item->log ? $item->log->status_description : '',
            ];
        });
    }

    public function with($request)
    {
        return  metaData(true, $request, '4025', 'success', 200, '');
    }
}
