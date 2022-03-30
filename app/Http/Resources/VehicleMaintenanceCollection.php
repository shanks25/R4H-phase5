<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VehicleMaintenanceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public $collects = VehicleMaintenanceResource::class;

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
    //             'vehicle_invoice_id' => $item->vehicle_invoice_id,
    //             'maintenance_request_id' => $item->maintenance_request_id,
    //             'driver_id' => $item->driver_id,
    //             'user_id' => $item->user_id,
    //             'vehicle_id' => $item->vehicle_id,
    //             'date' => $item->date,
    //             'service_details' => $item->service_details,
    //             'mileage' => $item->mileage,
    //             'other_service_details' => $item->other_service_details,
    //             'vehicle_number' => $item->vehicle_number,
    //             'other_details' => $item->other_details,
    //             'tax' => $item->tax,
    //             'total_due' => $item->total_due,
    //             'purchase_order' => $item->purchase_order,
    //             'shop_name' => $item->shop_name,
    //             'shop_contact_number' => $item->shop_contact_number,
    //             'work_completed_by' => $item->work_completed_by,
    //             'upload_invoice' => $item->upload_invoice,
    //             'invoice_date' => $item->invoice_date,
    //             'status' => $item->status,
    //             'request_date' => $item->request_date,
    //             'created_at' => $item->created_at,
    //             'updated_at' => $item->updated_at,
    //             'deleted_at' => $item->deleted_at,

    //             'vehicle' => $item->vehicle,
    //             'driver' => $item->driver,
    //             'invoices' => $item->invoices,
    //         ];
    //     });
    // }
}
