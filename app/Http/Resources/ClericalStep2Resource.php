<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClericalStep2Resource extends JsonResource
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
            'id'=>$this->id,
            'document1'=>$this->document1,
            'document1_image'=>$this->document1_image,
            'step2_doc1_expiry'=>$this->step2_doc1_expiry,
            'document2'=>$this->document2,
            'document2_image'=>$this->document2_image,
            'step2_doc2_expiry'=>$this->step2_doc2_expiry,
        ];
    }
}
