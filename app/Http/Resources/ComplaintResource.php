<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
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
            'created_at' => date('m/d/Y', strtotime($this->created_at)),
            'id'=>$this->id,
            'driver_id'=>$this->driver_id,
            'date'=>$this->date,
            'type'=>$this->type,
            'description'=>$this->description,
            'file'=>$this->upload,
            
        ];
    }
}
