<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'member_since' => date('m-d-Y', strtotime($this->created_at)),
            'member_name' => $this->name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile_no' => formatPhoneNumber($this->mobile_no),
            'mobile_no_2nd' => formatPhoneNumber($this->phone_2nd),
            'date_of_birth' => $this->dob,
            'active' => $this->active,
            'need_attendant' => $this->need_attendant,
            'promo_emails' => $this->promo_emails,
            'personal_notes' => $this->personal_notes,
            'on_hold' => $this->on_hold,
            'phone_auto_update' => $this->phone_auto_update,
            'emergency_contact' => $this->emergency_contact,
            'emergency_contact_name' => $this->emergency_contact_name,
            'isi' => $this->isi,
            'ssn' => $this->ssn,
            'no_show_raw' => $this->no_show_raw,
            'cin' => $this->cin,
            'weight' => $this->weight,
            'instructions' => $this->instructions,
            'minor' => $this->minor,
            'auto_calculation' => $this->auto_calculation,
            'was_confirmed' => $this->was_confirmed,
            'trip_purpose' => $this->trip_purpose,
            'wrong_number' => $this->wrong_number,
            'trips' => count($this->trips),
            'address' => $this->addresses,
            'primary_level_of_service' => $this->masterLevelOfService,

        ];
    }
}
