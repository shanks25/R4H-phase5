<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseLocationResource extends JsonResource
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
            'city_name' => $this->name,
            'city_id' => $this->city_id,
            'state' => $this->state,
            'address' => $this->address,
            'zipcode' =>  $this->zipcode,
            'default_location' => $this->is_default_location,
            'created_at' => date('m-d-Y',strtotime($this->created_at)),
          
        ];
    }
}
