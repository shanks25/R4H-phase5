<?php

namespace App\Http\Resources;

use App\Http\Resources\MemberAddressCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberTripResource extends JsonResource
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
            'first_name'=>$this->first_name,
            'middle_name'=>$this->middle_name,
            'last_name'=>$this->last_name,
            'dob'=>$this->dob,
            'ssn'=>$this->ssn,
            'mobile_no'=>$this->mobile_no,
            'masterLevelOfServices'=>$this->masterLevelOfService,
            'primary_payor_type_id'=>$this->primary_payor_type,
            'primaryPayor'=>$this->primaryPayor,
            'addresses'=>new MemberAddressCollection($this->addresses),

        ];
    }


    public function with($request)
    {
        $data = [
        'meta' => [
          'total' => 1
        ],
      ];
  
  
        $metaData = metaData(true, $request, '1003', 'success', '', '', '');
        return  merge($metaData, $data);
    }
}
