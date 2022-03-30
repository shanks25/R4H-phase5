<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
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
            'created_at'=>date('m/d/Y', strtotime($this->created_at)),
            'id'=>$this->id,
            'name'=>$this->name,
            'state'=>$this->state,
            'city'=>$this->city_id,
            'county'=>$this->county,
            'zipcode'=>$this->zipcode,
            
        ];
    }
}
