<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CrmResource extends JsonResource
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
            'phone_number' => $this->crm_mobile_no,
            'city' => $this->city,
            'name_city' => $this->name_city,
            'reg_since' => date('Y-m-d', strtotime($this->created_at)),
            'departments' => DepartmentResource::collection($this->departments),
            'street_address'=>$this->street_address,
            'lat'=>$this->lat,
            'lng'=>$this->lng,
            'zipcode'=>$this->zipcode,
        ];
    }
}
