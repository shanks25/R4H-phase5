<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleMaintenanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [

             'id' => $this->id,
             'vehicle_invoice_id' => $this->vehicle_invoice_id,
             'maintenance_type' => $this->maintenance_type,
             'driver_id' => $this->driver_id,
            'ticket_id' => $this->ticket_id,
             'user_id' => $this->user_id,
             'vehicle_id' => $this->vehicle_id,
             'date' => $this->date,
             'service_details' => $this->service_details,
             'mileage' => $this->mileage,
             'garage_id' => $this->garage_id,
             'other_service_details' => $this->other_service_details,
            // 'vehicle_number' => $this->vehicle_number,
             'other_details' => $this->other_details,
            // 'tax' => $this->tax,
            // 'total_due' => $this->total_due,
            // 'purchase_order' => $this->purchase_order,
            'shop_name' => $this->shop_name,
            'shop_contact_number' => $this->shop_contact_number,
            // 'work_completed_by' => $this->work_completed_by,
            // 'upload_invoice' => $this->upload_invoice,
            'invoice_date' => $this->invoice_date,
            // 'status' => $this->status,
            'request_date' => $this->request_date,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            // 'deleted_at' => $this->deleted_at,

            //   'services'=> $this->services,
            'vehicleMaintenanceService' => (new VehicleServiceMasterCollection($this->vehicleMaintenanceService)),
            // 'vehicle' => $this->vehicle,
            // 'driver' => $this->driver,
            'invoices' => $this->invoices,
        ];
    }
}
