<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AutoSetResource extends JsonResource
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
            'payor_type' => !empty($this->payorTypeNames->name)?$this->payorTypeNames->name:'',
            'payor_name' =>  !empty($this->payor->name)?$this->payor->name:"",
            'auto_set_time' => $this->auto_set_time,
            'created_at' => date('m-d-Y', strtotime($this->created_at)),
          
        ];
    }
}
