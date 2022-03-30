<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class exclusiveServiceResource extends JsonResource
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
            'date_of_service' => date("m/d/Y", strtotime($this->date_of_service)),
            'trip_id' => $this->trip_no,
            'member_name' => $this->member_name,
            'level_of_service' => $this->level_of_service ?? '',
            'total_unloaded_mileage' => $this->unloaded_miles,
            'unloaded_miles_duration' => $this->unloaded_miles_duration,
            'loaded_miles_duration'=>$this->loaded_miles_duration,
            'wait_time'=> gmdate("H:i:s", $this->wait_time),
            'invoice_amount'=> $this->total_amount ?? 0,
            'remaining_amount'=>$this->remaining_amount ?? 0,
            'paid_amount'=>$this->paid_amount ?? 0,
            'commission_amount'=>$this->commission_amount ?? 0,
            'admin_commision_amount'=>$this->admin_commision_amount ?? 0,
            'franchise_amount'=>$this->franchise_amount ?? 0,
            'sub_division_payable'=>$this->sub_division_payable ?? 0,
            'provider_invoice_status_id'=>$this->provider_invoice_status_id,
            'provider_remitances_status_id'=>$this->provider_remitances_status_id,
            'admin_status'=>$this->admin_status_id,
        ];
    }
}
