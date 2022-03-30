<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayorInfoResource extends JsonResource
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
            'invoice_number' => $this->invoice_no, 
            'invoice_date' => date("m/d/Y", strtotime($this->created_at)),
            'payor_type' => $this->payor_type,
            'payor_id' =>  $this->payor_id,
            'provider_total_amount' =>  $this->provider_total_amount,
        ];
    }
}
