<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClericalStep4Resource extends JsonResource
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
            'step4_new_date'=>$this->step4_new_date,
            'step4_revised_date'=>$this->step4_revised_date,
            'employee_name'=>$this->employee_name,
            'step4_address'=>$this->step4_address,
            'step4_phone_number'=>$this->step4_phone_number,
            'step4_cell_number'=>$this->step4_cell_number,
            'step4_dob'=>$this->step4_dob,
            'step4_ssn'=>$this->step4_ssn,
            'part_time'=>$this->part_time,
            'full_time'=>$this->full_time,
            'position'=>$this->position,
            'wage'=>$this->wage,
            'company_employment'=>$this->company_employment,
            'contractor_employment'=>$this->contractor_employment,
            'shirt_size'=>$this->shirt_size,
            'emergency_name'=>$this->emergency_name,
            'emergency_phone'=>$this->emergency_phone,
            'emergency_address'=>$this->emergency_address,
        ];
    }
}
