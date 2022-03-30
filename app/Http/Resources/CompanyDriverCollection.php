<?php

namespace App\Http\Resources;
use App\Traits\ResourceTrait;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CompanyDriverCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
   
    public function toArray($request)
    {
        return [
               'data'=>$this->map($this->collection) ,
        ]; 
    }


    public function with($request)
    {
      $data = [
        'meta' => [
          'total' => $this->collection->count()
        ],
      ];
  
  
        $metaData =  metaData(true, $request, '30035', 'success', 200, '');
        return  merge($metaData, $data);
   
    }


    public function map($collection)
    {
      return $collection->map(function ($item) {
        
        return [
                'id' => $item->id,
                'step_id' => $item->step->id,
                'created_at' => date('m-d-Y',strtotime($item->created_at)),
                'name' => !empty($item->name)? $item->name:'',
                'driver_type' => !empty($item->driver_type)? $item->driver_type:'',
                'email' =>  !empty($item->email)?$item->email:'',
                'step_1' => $item->step->step1_status,
                'step_2' => $item->step->step2_status,
                'step_3' => $item->step->step4_status,
                'step_4' => $item->step->step5_status,
                'percentage' => examscore($item->id),
                'step2_approve_status' => ($item->step->step2_approval_status==null)?0:$item->step->step2_approval_status,
                'status2_remark' => ($item->step->status2_remark==null)?'':$item->step->status2_remark,
                'step3_approve_status' => ($item->step->step4_approval_status==null)?0:$item->step->step4_approval_status,
                'status3_remark' =>($item->step->status4_remark==null)?'': $item->step->status4_remark,
                'step4_training_approval_status' =>($item->step->step5_training_approval_status==null)?0:$item->step->step5_training_approval_status,
                'step4_training_remark' => ($item->step->step5_training_remark==null)?'':$item->step->step5_training_remark,
                'step4_approve_status' => ($item->step->isstep5_approval_status_approved==null)?0:$item->step->step5_approval_status,
                'status4_remark' => ($item->step->status5_remark==null)?'':$item->step->status5_remark,
                'updated_at' =>  date('m-d-Y',strtotime($item->updated_at)),
                'note' => ($item->step->note==null)?'':$item->step->note,
                'final_aproval' => ($item->step->is_approved==null)?0:$item->step->is_approved,
                
                
                
           ];
         });
    }
}
