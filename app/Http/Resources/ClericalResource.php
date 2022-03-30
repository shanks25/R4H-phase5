<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClericalResource extends JsonResource
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
            'status'=>$this->overall_status,
            'updated_at'=>date('m/d/Y',strtotime($this->updated_at)),
            'full_name'=>$this->full_name,
            'email'=>$this->job->email,
            'home_telephone'=>$this->home_telephone,
            'address'=>$this->address,
            'step1'=>url('clerical-step1',$this->id),
            'step2'=>url('clerical-step-point2',$this->id),
            'step3'=>url('clerical-step-point3',$this->id),
            'step4'=>url('clerical-step-point4',$this->id),
            'is_step3_approved'=>$this->is_step3_approved,
            'step3_remarks'=>$this->step3_approve_reject_status_remark,
            'step4_approval_status'=>$this->is_final_approved,
            'step4_remarks'=>$this->final_approve_reject_status_remark,
            'note'=>$this->note,


        ];
    }
}
