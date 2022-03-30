<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EsoResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'mobile_no' => $this->mobile_no,
            'address' => $this->address,
            'timezone' => $this->timezone,
            'representative_name' => $this->representative_name,
            'google_api_key' => $this->google_key,
        ];
    }
}
