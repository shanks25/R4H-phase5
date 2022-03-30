<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BillingInvoiceResource extends JsonResource
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
            'invoice_date' => $this->invoice_date,
            'payor_type' => $this->payor_type ?? '',
            'payor_name' => $this->payor_name,
            'total_trips' => count($this->items),
            'total_trips_link' => url('exclusive-service-operator',$this->id),
            'total_invoice_amount'=>$this->provider_total_amount ?? 0,
            'total_amount_pending'=>$this->franchise_remaining_amount ?? 0,
            'total_amount_paid'=>$this->franchise_paid_amount ?? 0,
            'invoice_status'=>$this->franchise_payment_status,
            'invoice_download'=> url('download-invoice', $this->id),
        ];
    }
}
