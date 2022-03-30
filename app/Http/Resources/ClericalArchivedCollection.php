<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\ClericalResource;

class ClericalArchivedCollection extends ResourceCollection
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
  
  
        $metaData =  metaData(true, $request, '30042', 'success', 200, '');
        return  merge($metaData, $data);
   
    }

    public function map($collection)
    {
      return $collection->map(function ($item) {
        
        return [
                'id' => $item->id,
                'job_id' => $item->job_id,
                'request_date' => date('m-d-Y',strtotime($item->created_at)),
                'aplicant_name' => !empty($item->job->name)?$item->job->name:'',
                'email' => !empty($item->job->email)?$item->job->email:'',
                'home_telephone' => formatPhoneNumber($item->home_telephone),
                'address' => $item->address,
                'step1_status' => $item->step1_status,
                'step2_status' => $item->step2_status,
                'step3_status' => $item->step3_status,
                'step4Point1_status' => $item->step4Point1_status,
                'step1_point3_status' => $item->step1_point3_status,
                'step3_approve_reject_status_remark' => $item->step3_approve_reject_status_remark,
                'step4Point3_status' => $item->step4Point3_status,
                'is_step3_approved' => $item->is_step3_approved,
                'is_final_approved' => $item->is_final_approved,
               ];
         });
    }
}


