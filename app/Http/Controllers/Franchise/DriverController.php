<?php

namespace App\Http\Controllers\Franchise;

use App\Models\DriverZones;
use App\Models\DriverMaster;
use Illuminate\Http\Request;
use App\Models\InsuranceType;

use Illuminate\Validation\Rule;
use App\Models\DriverRequestStep;
use App\Models\Driverleavedetails;
use App\Http\Controllers\Controller;
use App\Models\DriverLevelofService;
use App\Http\Resources\DriverResource;
use App\Http\Resources\DriverCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AvailabilityRequest;
use App\Http\Requests\DriverUpdateRequest;
use App\Http\Resources\DriverListResource;
use App\Http\Resources\DriverViewResource;
use App\Http\Requests\DriverPersonalRequest;
use App\Http\Resources\DriverListCollection;
use App\Models\DriverIdentificationDocuments;
use App\Http\Requests\DriverCredentialRequest;
use App\Http\Resources\DriverLeavesCollection;
use App\Http\Requests\DriverWorkProfileRequest;
use App\Http\Requests\DriverProfessionalRequest;

class DriverController extends Controller
{
    public function  get(Request $request)
    {
        
        $driver =  DriverMaster::select('id', 'name')
            ->where('user_id', $request->eso_id)
            ->orderBy('name', 'ASC')
            ->get();
        return new DriverCollection($driver);
    }

    public function  getInsuranceType(Request $request)
    {
        try {
        $insurncetype =  InsuranceType::select('id', 'name')
            ->orderBy('name', 'ASC')
            ->get();
            $metaData= metaData(true, $request, '30040', 'success', 200, '');
        return merge(['data'=>$insurncetype ], $metaData);
    } catch (\Exception $e) {
        return metaData(false, $request, 30040, '', 502, errorDesc($e), 'Error occured in server side ');
    }
    }

    public function index(Request $request)
    {
       
        try {
            $query = DriverMaster::eso()->with('vehicle:id,model_no')->where('user_id', $request->eso_id);
            $driver=DriverMaster::filterDriver($request, $query)
            ->latest()->paginate(config('Settings.pagination'));
            return new DriverListCollection($driver);
        } catch (\Exception $e) {
            return metaData(false, $request, 30026, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

     /*--------------------- Add driver---------------- */

     public function store(DriverPersonalRequest $request)
     {
         
        $name = $request->first_name;
        if ($request->filled('middle_name')) {
            $name .= ' ' . $request->middle_name;
        }
        $name .= ' ' . $request->last_name;
           
         $request-> merge(['name' => $name ]);
                 
         try {
             $driver=DriverMaster::Create($request->all());
            
             $metaData= metaData(true, $request, '30027', 'success', 200, '');
             return (new DriverListResource($driver))->additional($metaData);
             
         } catch (\Exception $e) {
             return metaData(false, $request, 30027, '', 502, errorDesc($e), 'Error occured in server side ');
         }
     }
 
     /*---------------------End Add driver---------------- */


     /*---------------------Edit driver---------------- */

     public function edit(Request $request)
     {
         $validator = Validator::make($request->all(), [
         'id' => ['required', Rule::exists('driver_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
                 
         ], [
             'id.required' => 'ID is required.',
             'id.exists' => 'Invalid ID',
             
         ]);
         if ($validator->fails()) {
             return $metaData = metaData('false', $request, '30028', '', '502', '', $validator->messages());
         }
         
         try {
             $member=DriverMaster::find($request->id);
                       
             $metaData= metaData(true, $request, '30028', 'success', 200, '');
             return  (New DriverResource($member))->additional($metaData);
            
         } catch (\Exception $e) {
             return metaData(false, $request, 30028, '', 502, errorDesc($e), 'Error occured in server side ');
         }
     }
 

      /*---------------------End Edit driver---------------- */
      
      /*---------------------Update driver---------------- */

    public function update(DriverUpdateRequest $request)
    {
       
        
        $name = $request->first_name;
        if ($request->filled('middle_name')) {
            $name .= ' ' . $request->middle_name;
        }
        $name .= ' ' . $request->last_name;
         
          
         
    
        try {
            $request-> merge(['updated_at' => now()]);
            $request-> merge(['name' => $name ]);
            $input=$request->except('password_confirm', 'eso_id');
        
            $driver= DriverMaster::find($request->id);
            $driver->update($input);
            $metaData= metaData(true, $request, '30029', 'success', 200, '');
            return (new DriverListResource($driver))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '30029', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    /*---------------------End Update driver---------------- */



     /*---------------------Edit driver---------------- */

     public function view(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'id' => ['required', Rule::exists('driver_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
                 
         ], [
             'id.required' => 'ID is required.',
             'id.exists' => 'Invalid ID',
             
         ]);
         if ($validator->fails()) {
             return $metaData = metaData('false', $request, '30036', '', '502', '', $validator->messages());
         }
         
         try {
             $driver=DriverMaster::find($request->id);
            
             $metaData= metaData(true, $request, '30036', 'success', 200, '');
             return  (New DriverViewResource($driver))->additional($metaData);
            
         } catch (\Exception $e) {
             return metaData(false, $request, 30036, '', 502, errorDesc($e), 'Error occured in server side ');
         }
     }
 

      /*---------------------End Edit driver---------------- */
      

    
   /*--------------------- Add driver professional---------------- */

   public function professionalStore(DriverProfessionalRequest $request)
   {
    $upload_documents_path = '';
            
    if ($request->hasFile('documents_file')) {
        $upload_documents_path =   upload($request->file('documents_file'), '/storage/uploads/vehicle');
    }
    
       $request-> merge(['upload_signature_original' => $upload_documents_path ]);
   
      
       try {
           
            $driver= DriverMaster::where('id',$request->id)->update($request->except(['eso_id','upload_signature','service_id']));
           
            $driver=DriverMaster::find($request->id);
            $driver->driverLevelservices()->sync($request->service_id);
           
           $metaData= metaData(true, $request, '30030', 'success', 200, '');
           return (new DriverListResource($driver))->additional($metaData);
       } catch (\Exception $e) {
           return metaData(false, $request, 30030, '', 502, errorDesc($e), 'Error occured in server side ');
       }
   }

   /*---------------------End Add driver professional---------------- */

   /*--------------------- Add driver professional---------------- */

   public function workProfileStore(DriverWorkProfileRequest $request)
   {
      
       try {
           
          DriverMaster::where('id',$request->id)->update($request->except(['eso_id','zone']));
          DriverMaster::find($request->id)->driverZones()->sync($request->zone);
          $driver=DriverMaster::find($request->id);
        
           $metaData= metaData(true, $request, '30031', 'success', 200, '');
           return (new DriverListResource($driver))->additional($metaData);
       } catch (\Exception $e) {
           return metaData(false, $request, 30031, '', 502, errorDesc($e), 'Error occured in server side ');
       }
   }

   /*---------------------End Add driver professional---------------- */


   /*--------------------- Add driver professional---------------- */

   public function availabilityStore(AvailabilityRequest $request)
   {
       
    
       try {
           $dataArray=array();
           foreach($request->start_date as $k=>$start_date)
           {
            $dataArray=[
                'driver_id'=>$request->id,
                'start_date'=>$start_date,
                'end_date'=>$request->end_date[$k],
                'status'=>$request->status,
                'resume_date'=>$request->resume_date,
                'reason'=>$request->reason,
                'created_at'=>now(),
                'updated_at'=>now(),
            ];
           }
       
          Driverleavedetails::insert($dataArray);
          $driver=DriverMaster::find($request->id);
           $metaData= metaData(true, $request, '30032', 'success', 200, '');
           return (new DriverListResource($driver))->additional($metaData);
       } catch (\Exception $e) {
           return metaData(false, $request, 30032, '', 502, errorDesc($e), 'Error occured in server side ');
       }
   }

   /*---------------------End Add driver professional---------------- */
   

   
   /*--------------------- Add driver professional---------------- */

   public function creadentialsStore(DriverCredentialRequest $request)
   {
      
      // try {
           $dataArray=array();
           foreach($request->dynamic_identification_id as $k=>$identification_id)
           {
            $identification_file="";
            $custome_name = 'dynamic_identification_file_' . $k;
            if (!empty($request->file($custome_name))) {
                $identification_file = upload($request->file($custome_name), 'requestform/images');
            }
            $dataArray=[
                'driver_id'=>$request->driver_id,
                'identification_id'=>$identification_id,
                'identification_expity'=>$request->dynamic_identification_expiry[$k],
                'identification_file'=> $identification_file ,
            ];
           
           }  
            DriverIdentificationDocuments::insert($dataArray);
           
       
            if ($request->hasFile('oig_doc')) {
                $imageName1 = upload($request->file('oig_doc'), 'requestform/images');
                $request-> merge(['oig' =>$imageName1]);
            }

            if ($request->hasFile('sam_gov_doc')) {
                $imageName1 = upload($request->file('sam_gov_doc'), 'requestform/images');
              
                $request-> merge(['sam_gov' =>$imageName1]);
            }
            if ($request->hasFile('national_criminal_bk_check_doc')) {
                $imageName1 = upload($request->file('national_criminal_bk_check_doc'), 'requestform/images');
                $request-> merge(['national_criminal_bk_check' =>$imageName1]);
            }
            if ($request->hasFile('sex_offender_report_doc')) {
                $imageName1 = upload($request->file('sex_offender_report_doc'), 'requestform/images');
                $request-> merge(['sex_offender_report' =>$imageName1]);
            }
            if ($request->hasFile('motor_vehicle_record_doc')) {
                $imageName1 = upload($request->file('motor_vehicle_record_doc'), 'requestform/images');
                $request-> merge(['motor_vehicle_record' =>$imageName1]);
            }
            if ($request->hasFile('child_abuse_clearance_doc')) {
                $imageName1 = upload($request->file('child_abuse_clearance_doc'), 'requestform/images');
                $request-> merge(['child_abuse_clearance' =>$imageName1]);
            }

            if ($request->hasFile('upload_tlc')) {
                $imageName1 = upload($request->file('upload_tlc'), 'requestform/images');
                $request-> merge(['tlc_upload' =>$imageName1]);
            }

          
            if ($request->hasFile('hippa_upload')) {
                $imageName1 = upload($request->file('hippa_upload'), 'requestform/images');
                $request-> merge(['upload_hippa' =>$imageName1]);
            }

           
            if ($request->hasFile('upload_drug')) {
                $imageName1 = upload($request->file('upload_drug'), 'requestform/images');
                $request-> merge(['drug_test_result' =>$imageName1]);
            }

           
            if ($request->hasFile('upload_defensive')) {
                $imageName1 = upload($request->file('upload_defensive'), 'requestform/images');
                $request-> merge(['defensive_upload' =>$imageName1]);
            }

           $expcted=['id','eso_id','dynamic_identification_file','dynamic_identification_expiry','dynamic_identification_id','cpr','first_aid','mvr','user_id'];
           $d_steps = DriverRequestStep::where('driver_id', $request->driver_id)->first();
           if(empty($d_steps))
           {
            DriverRequestStep::insert($request->except($expcted));
            }
            else
            {
                DriverRequestStep::where('driver_id', $request->driver_id)->update($request->except($expcted));
            }
            
          $driver=DriverMaster::find($request->driver_id);
          $driver->update(['cpr'=>$request->cpr,'first_aid'=>$request->first_aid,'mvr'=>$request->mvr]);
           $metaData= metaData(true, $request, '30032', 'success', 200, '');
           return (new DriverListResource($driver))->additional($metaData);
    //    } catch (\Exception $e) {
    //        return metaData(false, $request, 30032, '', 502, errorDesc($e), 'Error occured in server side ');
    //    }
   }

   /*---------------------End Add driver professional---------------- */
   
   
   /*---------------------Assign vehicle to driver---------------- */

   public function assignVehicle(Request $request)
   {
    $validator = Validator::make($request->all(), [
        'driver_id' => ['required', Rule::exists('driver_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
        'vehicle_id' => 'nullable|numeric',
            
    ], [
        'driver_id.required' => 'Driver ID is required.',
        'driver_id.exists' => 'Invalid Driver ID',
        
        
    ]);
    if ($validator->fails()) {
        return metaData('false', $request, '30033', '', '502', '', $validator->messages());
    }

      
       $request-> merge(['updated_at' =>now()]);
      
       try {
           
            $driver= DriverMaster::where('id',$request->driver_id);
            $driver ->update($request->except(['eso_id','driver_id']));
            $driver=DriverMaster::find($request->driver_id);
            $metaData= metaData(true, $request, '30033', 'success', 200, '');            
            return (new DriverListResource($driver))->additional($metaData);
       } catch (\Exception $e) {
           return metaData(false, $request, 30033, '', 502, errorDesc($e), 'Error occured in server side ');
       }
   }
      
   /*---------------------Assign vehicle to driver---------------- */

  
   /*---------------------Assign vehicle to driver---------------- */

   public function changeStatus(Request $request)
   {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'array|min:1',
            'driver_id.*' =>  ['required', Rule::exists('driver_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
            'driver_id.*' => 'required|numeric|exists:driver_master_ut,id,deleted_at,NULL',
            'status' => 'required|in:0,1',
                
        ], [
            'driver_id.required' => 'Driver ID is required.',
            'driver_id.exists' => 'Invalid Driver ID',
            
            
        ]);
        if ($validator->fails()) {
            return metaData('false', $request, '30035', '', '502', '', $validator->messages());
        }

      
       try {
           
        DriverMaster::whereIn('id',$request->driver_id)->update(['status'=>$request->status,'updated_at' =>now()]);
            $metaData= metaData(true, $request, '30035', 'success', 200, '');            
            return merge(['data'=>['driver_id'=>$request->driver_id,'status'=>$request->status]],$metaData);
       } catch (\Exception $e) {
           return metaData(false, $request, 30035, '', 502, errorDesc($e), 'Error occured in server side ');
       }
   }
      
   /*---------------------Assign vehicle to driver---------------- */


   
    /*---------------------Driver Leaves---------------- */

    public function driverLeaves(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id' =>['required', Rule::exists('driver_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
                
        ], [
            'driver_id.required' => 'ID is required.',
            'driver_id.exists' => 'Invalid ID',
            
        ]);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30034', '', '502', '', $validator->messages());
        }
        
        try {
            $driverLeaves=Driverleavedetails::where('driver_id',$request->driver_id)->where('status','!=',0)->get();
                      
            $metaData= metaData(true, $request, '30034', 'success', 200, '');
            return  (New DriverLeavesCollection($driverLeaves))->additional($metaData);
           
        } catch (\Exception $e) {
            return metaData(false, $request, 30034, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }


     /*---------------------End Driver Leaves---------------- */
   
    /*---------------------delete driver---------------- */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'id' => 'required|required|array|min:1',
        'id.*' =>['required', Rule::exists('driver_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
            ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);

        if ($validator->fails()) {
            return metaData('false', $request, '30027', '', '502', '', $validator->messages());
        }

        try {
            
            DriverZones::whereIn('driver_id', $request->id)->delete();
            Driverleavedetails::whereIn('driver_id',$request->id)->delete();
            DriverLevelofService::whereIn('driver_id',$request->id)->delete();
            $driver= DriverMaster::whereIn('id',$request->id)->delete();
            
            $metaData=metaData(true, $request, '30027', 'success', 200, '');
            return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '30027', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    
    /*---------------------End delete driver---------------- */
}
