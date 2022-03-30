<?php

namespace App\Http\Controllers\Franchise;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\DriverMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\DriverRequestStep;
use App\Rules\ValidatePayorIdRule;
use App\Http\Controllers\Controller;
use Facade\FlareClient\Http\Response;
use App\Models\Driverrequestemployees;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CompanyDriverResource;
use App\Models\Driverrequestreferencescontact;
use App\Http\Resources\CompanyDriverCollection;

class HiringDriverController extends Controller
{
    
    /*---------------------Auto Set List---------------- */

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_type' => 'required|in:Company,ISP'
            
        ], [
            'driver_type.required' => 'Driver Type is required.',
            
            
        ]);

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30035', '', '502', '', $validator->messages());
        }
        try {

            $query = DriverMaster::eso();
            $driver= DriverMaster::filterCompanayDriver($request, $query);
            $driver=$driver->latest()->paginate(config('Settings.pagination'));
            return (new CompanyDriverCollection($driver));

        } catch (\Exception $e) {
            return metaData(false, $request, 30035, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function detail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'driver_id' =>['required', Rule::exists('driver_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
                
        ], [
            'driver_id.required' => 'Driver ID is required.',
            'driver_id.exists' => 'Invalid Driver ID',
            
        ]);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30036', '', '502', '', $validator->messages());
        }   
        $driver = DriverMaster::with('step')
        ->with('step5:id,passenger_assistance_training_name,confirm_passenger_assistance_training_certificate,confirm_passenger_assistance_training_certificate_name,confirm_passenger_assistance_training_certificate_date,step5_15_signature,step5_14_confirm')
        ->with('employees')
        ->with('user:id,name')
        ->with('states:id,name')
        ->with('city:id,city')
        ->with('county:id,name')
        ->with('zipcode:id,zip')
        ->with('referencescontact')
        ->where('id', $request->driver_id)->eso()->first();
        
        return (new CompanyDriverResource($driver));
    }

    public function ChangeDriverApproveStatus(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'status' =>'required|in:0,1,2,3',
            'inner_step_id' =>  ['required', Rule::exists('driver_request_form_steps', 'id,deleted_at,NULL')], 'eso_id'=>['required',Rule::exists('driver_master_ut','user_id,deleted_at,NULL')], 
            'inner_step_no' =>'required|in:2,4,5,5_13', 
            'status_remark' =>'required|max:150',
            'driver_type' => 'required|in:Company,ISP' 
        ], [
            'step_id.required' => 'Driver ID is required.',
            'step_id.exists' => 'Invalid Driver ID',
            
        ]);

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30037', '', '502', '', $validator->messages());
        }  

      
        $key=$request->inner_step_id;
        $driver = DriverMaster::select('*')->where('user_id',esoId())->where('driver_type',$request->driver_type)->whereHas('step',function ($query) use ($key) {
            $query->where('id',$key);
           })->first();
        if (!$driver) {
          
            return $metaData = metaData('false', $request, '30037', '', '502', '', 'Invalid Step ID.');
        }

        $step = DriverRequestStep::find($request->inner_step_id);
        $step->updated_at = Carbon::now();

        
        $driver = DriverMaster::where('id', $step->driver_id)->first();
        $driver_name = $driver->name;
        $driver_email = $driver->email;
        $is_approved = $driver->is_approved;
        $subdivision_id = $driver->user_id;
        $password = $driver->password;
        $driver_type = $driver->driver_type;

        //for resubmit
        if ($request->status == 3)
        {
            if ($request->inner_step_no == 2) {
                $step->step2_approval_status = $request->status;
                $step->status2_remark = $request->status_remark;
                $step->overall_status = 1;
                $step->rejected_date = null;

                $driverdata = array(
                    'is_approved' => 1, //pending
                    'status' => '0',
                );
                DriverMaster::where('is_approved', 3)->where('id', $step->driver_id)->update($driverdata);
    
            } elseif ($request->inner_step_no == 4) {
                $step->step4_approval_status = $request->status;
                $step->status4_remark = $request->status_remark;
                $step->overall_status = 1;
                $step->rejected_date = null;

                $driverdata = array(
                    'is_approved' => 1, //pending
                    'status' => '0',
                );
                DriverMaster::where('is_approved', 3)->where('id', $step->driver_id)->update($driverdata);

            } elseif ($request->inner_step_no == '5_13') { //training_approval
                $step->step5_training_approval_status = $request->status;
                $step->step5_training_remark = $request->status_remark;
                $step->overall_status = 1;
                $step->rejected_date = null;

                $driverdata = array(
                    'is_approved' => 1, //pending
                    'status' => '0',
                );
                DriverMaster::where('is_approved', 3)->where('id', $step->driver_id)->update($driverdata);
            }
            elseif ($request->inner_step_no == '5') {
                $step->step5_approval_status = $request->status;
                $step->status5_remark = $request->status_remark;
                $step->overall_status = 1;
                $step->rejected_date = null;

                $driverdata = array(
                    'is_approved' => 1, //pending
                    'status' => '0',
                );
                DriverMaster::where('is_approved', 3)->where('id', $step->driver_id)->update($driverdata);
            }
            $step->save();


            //sent email
            $subdivision = User::where('id', $subdivision_id)->first();
            $subdivision_name = $subdivision->name;
            $subdivision_email = $subdivision->email;

            $admin = Admin::where('id', 1)->first();
            $admin_name = $admin->name;
            $admin_email = $admin->email;

            $data = array('driver_name' => $driver_name, 'subdivision_name' => $subdivision_name, 'admin_name' => $admin_name, 'admin_email' => $admin_email, 'driver_email' => $driver_email, 'subdivision_email' => $subdivision_email, 'inner_step_no' => $request->inner_step_no, 'status_remark' => $request->status_remark, 'is_approved' => $is_approved, 'password' => $password, 'current_step_status' => $request->status, 'driver_type' => $driver_type);
            sent_email_driver_step_message($data);

            if($request->inner_step_no == 2)
            {
                $msg = $driver_name.' - Feedback sent for Steps-2';
            }
            else if($request->inner_step_no == 4)
            {
                $msg = $driver_name.' - Feedback sent for Steps-3';
            }
            else if($request->inner_step_no == '5_13')
            {
                $msg = $driver_name.' - Feedback sent for Steps-4 Training';
            }
            else if($request->inner_step_no == 5)
            {
                $msg = $driver_name.' - Feedback sent for Steps-4';
            }
           
            return $metaData=metaData(true, $request, '30037',  $msg , 200, '');
        }



        //change status
        if ($request->inner_step_no == 2) {
            if ($step->step2_status == 0) {
                return metaData(false, $request, '30037', '', 502, '', 'Step 2 not completed yet. ');
            }

            if ($step->step4_approval_status == 1) {
                 return metaData(false, $request, '30037', '', 502,'', 'You have already approved Next steps.');
            }

            $step->step2_approval_status = $request->status;
            $step->status2_remark = $request->status_remark;

            $overall_status = 0; //pending
            if ($request->status == 1) {
                $overall_status = 1; //Application In Process
            } elseif ($request->status == 2) {
                $overall_status = 3; //reject
            }
            $step->overall_status = $overall_status;
        } 
        elseif ($request->inner_step_no == 4) {
            if ($step->step4_status == 0) {
               return metaData(false, $request, '30037', '', 502,'', 'Step 4 not completed yet.');
            }

            if ($step->step5_approval_status == 1) {
                return metaData(false, $request, '30037', '', 502,'', "You have already approved Next steps.");
            }

            $step->step4_approval_status = $request->status;
            $step->status4_remark = $request->status_remark;

            $overall_status = 1; //Application In Process
            if ($request->status == 2) {
                $overall_status = 3; //reject
            }
            $step->overall_status = $overall_status;
        
        } elseif ($request->inner_step_no == '5_13') {
            if ($step->step4_status == 0) {
                return metaData(false, $request, '30037', '', 502,'', 'Step 4 not completed yet.');
            }

            if ($step->step5_approval_status == 1) {
                return metaData(false, $request, '30037', '', 502,'', "You have already approved Next steps.");
            }

            $step->step5_training_approval_status = $request->status;
            $step->step5_training_remark = $request->status_remark;

            $overall_status = 1; //Application In Process
            if ($request->status == 2) {
                $overall_status = 3; //reject
            }
            $step->overall_status = $overall_status;
        }
        elseif ($request->inner_step_no == 5) {
            if ($step->step2_approval_status != 1 && $request->status == 1) {
               return metaData(false, $request, '30037', '', 502,'', 'Step 2 not approved yet.');
            }

            if ($step->step4_approval_status != 1 && $request->status == 1) {
                return metaData(false, $request, '30037', '', 502,'', 'Step 3 not approved yet.');
               
            }

            if($driver_type == 'Company')
            {
                if ($step->step5_training_approval_status != 1 && $request->status == 1) {
                return metaData(false, $request, '30037', '', 502,'',  'Training on Step 4 not approved yet.');
                }
            }

            if ($step->step5_status == 0) {
                return metaData(false, $request, '30037', '', 502,'',  'Step 5 not completed yet.');
            }

            $driver = DriverMaster::where('id', $step->driver_id)->first();
          

            if ($request->status == 1) {
                $score_in_percentage = examscore($step->driver_id);
                if ($score_in_percentage['exam_is_done'] == 1 && $score_in_percentage['percentage'] <= 85)
                 {
                    return metaData(false, $request, '30037', '', 502,'',  'Final Exam score is less than %85');
                } 
                elseif ($score_in_percentage['exam_is_done'] == 0) 
                {
                    return metaData(false, $request, '30037', '', 502,'', 'Final Exam is not submitted successfully yet.');
                }
            }

           
            $step->status5_remark = $request->status_remark;
            $step->step5_approval_status = $request->status;

            $overall_status = 1; //Application In Process
            if ($request->status == 1) {
                $overall_status = 2; //hired
            } elseif ($request->status == 2) {
                $overall_status = 3; //reject
            }
            $step->overall_status = $overall_status;

            if ($request->status == 0) {
                $driverdata = array(
                    'is_approved' => 1, //pending
                    'status' => '0',
                );
                DriverMaster::where('id', $step->driver_id)->update($driverdata);
            } 
         
            elseif ($request->status == 2) {
                $driverdata = array(
                    'is_approved' => 3, //reject
                    'status' => '0',
                );
                DriverMaster::where('id', $step->driver_id)->update($driverdata);
            }
        }
        $step->save();


        if ($request->status == 2) {
            $driverdata = array(
                'is_approved' => 3, //reject
            );
            DriverMaster::where('id', $step->driver_id)->update($driverdata);

            $driverdata = array(
                'rejected_date' => Carbon::now(), //reject datetime
            );
            DriverRequestStep::where('id', $request->inner_step_id)->update($driverdata);
        }
         elseif ($step->step2_approval_status == 0 || $step->step4_approval_status == 0 || $step->step5_approval_status == 0 || $step->step5_training_approval_status == 0) 
         {
            $driverdata = array(
                'is_approved' => 1, //pending
            );
            DriverMaster::where('id', $step->driver_id)->update($driverdata);

            $driverdata = array(
                'rejected_date' => null, //reject datetime
            );
            DriverRequestStep::where('id', $request->inner_step_id)->update($driverdata);
        }
       

        //sent email
        $driver = DriverMaster::where('id', $step->driver_id)->first();
        $driver_name = $driver->name;
        $driver_email = $driver->email;
        $is_approved = $driver->is_approved;
        $subdivision_id = $driver->user_id;
        $password = $driver->password;
        $driver_type = $driver->driver_type;

        $subdivision = User::where('id', $subdivision_id)->first();
        $subdivision_name = $subdivision->name;
        $subdivision_email = $subdivision->email;

        $admin = Admin::where('id', 1)->first();
        $admin_name = $admin->name;
        $admin_email = $admin->email;

        $data = array('driver_name' => $driver_name, 'subdivision_name' => $subdivision_name, 'admin_name' => $admin_name, 'admin_email' => $admin_email, 'driver_email' => $driver_email, 'subdivision_email' => $subdivision_email, 'inner_step_no' => $request->inner_step_no, 'status_remark' => $request->status_remark, 'is_approved' => $is_approved, 'password' => $password, 'current_step_status' => $request->status, 'driver_type' => $driver_type);
        sent_email_driver_step_eso_approval($data);
        return $metaData=metaData(true, $request, '30037',  'Status updated successfully.' , 200, '');
        
    }


    public function adddriverrequestnote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'step_id' =>  ['required', Rule::exists('driver_request_form_steps', 'id,deleted_at,NULL')], 'eso_id'=>['required',Rule::exists('driver_master_ut','user_id,deleted_at,NULL')], 
            'note'=>'required|max:150'
                      
        ], [
            'step_id.required' => 'Step ID is required.',
            'step_id.exists' => 'Invalid Step ID',
            
        ]);

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30038', '', '502', '', $validator->messages());
        }
        $key=$request->step_id;
        $driver = DriverMaster::select('*')->where('user_id',esoId())->whereHas('step',function ($query) use ($key) {
            $query->where('id',$key);
           })->first();
        if (!$driver) {
          
            return $metaData = metaData('false', $request, '30038', '', '502', '', 'Invalid Step ID.');
        }

        $step = DriverRequestStep::find($request->step_id);
        $step->note = $request->note;
        $step->save();
        $metaData= metaData(true, $request, '30038', 'success', 200, '');
       $note= ['step_id'=>$request->step_id,'note'=>$request->note];
        return merge(['data'=>$note],$metaData);
    }

  

    
}


