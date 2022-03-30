<?php

namespace App\Http\Resources;
use App\Models\Vehicle;
use Illuminate\Http\Resources\Json\JsonResource;
class VehicleServiceInvoiceResource extends JsonResource
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
                'invoice_no' => $this->invoice_no,
                'ticket_id' => $this->ticket_id,
                'service_date' => date('m-d-Y',strtotime($this->service_date)),
                'odometter_upon_service' => $this->odometter_upon_service,
                'purchase_order' => $this->purchase_order,
                'warranty_information' => $this->warranty_information,
                'spacial_instructions' => $this->spacial_instructions,
                'tax' => $this->tax,
                'sub_total' => $this->sub_total,
                'total' => $this->total,
                'user_id' => $this->user_id,
                'created_at' => date('m-d-Y',strtotime($this->created_at)),
                'service_item_charge' => $this->ServiceItemCharge,
                
        ];
    }
}
