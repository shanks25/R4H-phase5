<?php
namespace App\Http\Controllers\Franchise;
use App\Mail\CommonMail;
use App\Models\Admin;
use Carbon\Carbon;
use App\Models\Clerical;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\clericalPageResource;
use App\Http\Resources\ClericalPageCollection;
use App\Http\Resources\ClericalStep2Resource;
use App\Http\Resources\ClericalStep2Collection;
use App\Http\Resources\ClericalStep4Resource;
use App\Http\Resources\ClericalStep4Collection;
use App\Http\Resources\ClericalStep3Resource;
use App\Http\Resources\ClericalStep3Collection;
use App\Http\Resources\Step4ApprovalResource;
use App\Http\Resources\Step3approvalResource;
use App\Http\Resources\Step3approvalCollection;
use App\Http\Resources\Step4ApprovalStatusCollection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class ClericalPageStepController extends Controller
{
	public function step1(Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return (new ClericalPageCollection($clerical));

	}

	public function step1Point2 (Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalPageCollection($clerical);
	}

	public function step1Point3 (Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalPageCollection($clerical);
	}

	public function step2(Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalPageCollection($clerical);
	}

	public function step3(Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalPageCollection($clerical);
	}

	public function step4(Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalPageCollection($clerical);
	}

	public function step4Point2(Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalPageCollection($clerical);
	}

	public function step4Point3(Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalPageCollection($clerical);
	}

	public function clericalstep2(Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalStep2Collection($clerical);
	}

	public function clericalstep3(Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalStep3Collection($clerical);
	}

	public function clericalstep4Point(Request $request)
	{
		$clerical =	Clerical::with('previousEmployeers')->where('user_id',$request->eso_id)->where('id',$request->id)->get();
		return new ClericalStep4Collection($clerical);
	}
	
	public function step3ApprovalStatus(Request $request)
	{    
		$validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
			'is_step3_approved'=>'required|numeric|gt:0|between:1,3',
            
        ], 
		);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30009', '', '502', '', $validator->messages());
        }
		
		
		try{
	
            $request-> merge(['user_id' => $request->eso_id]);
            $input=$request->except('eso_id');
            $clerical = Clerical::find($request->id);
			$clerical->is_step3_approved = $request->is_step3_approved;
            $clerical->update($input);
            
				
			$franchise_user_name = $clerical->eso->name;
			$franchise_user_email = $clerical->eso->email;
			$data = array();
			$data['email'] = $franchise_user_email;
			$data['name'] = $franchise_user_name;
			$data['bodytext'] = '<p>Dear ' . $franchise_user_name . ',</p></br></br>
			<p>'.$clerical->full_name.' has completed the Step-3 of the Clerical application, Kindly review the same and take the necessary action.</p>';
			$view = 'mails.admin.commonmailbody';
			$subject = 'Clerical Job Application';
			// print_r($data);die;
	
			Mail::to($franchise_user_email)->send(new CommonMail($franchise_user_name, $view, $subject, $data));

			// admin
			$admin = Admin::first();
			$admin_name = $admin->name;
			$admin_email = $admin->email;
			$data = array();
			$data['email'] = $admin_email;
			$data['name'] = $admin_name;
			$data['bodytext'] = '<p>Dear Admin,</p></br></br>
			<p>'.$clerical->full_name.' has completed the Step-3 of the Clerical application, Kindly review the same and take the necessary action.</p>';
			$view = 'mails.admin.commonmailbody';
			$subject = 'Clerical Job Application';
	
			Mail::to($admin_email)->send(new CommonMail($admin_name, $view, $subject, $data));

			// Job applicant

		  
			$clerical_name = $clerical->full_name;
			$clerical_email = $clerical->job->email;
			$data = array();
			$data['email'] = $clerical_email;
			$data['name'] = $clerical_name;
			$data['bodytext'] = '<p>Dear ' . $clerical_name . ',</p></br></br>
			<p>You have completed step-3 of the application. We will review your application and get back to you shortly.</p>';
			$view = 'mails.admin.commonmailbody';
			$subject = 'Clerical Job Application';
	
			Mail::to($clerical_email)->send(new CommonMail($clerical_name, $view, $subject, $data));



			$metaData= metaData(true, $request, '5010', 'success', 200, '');
            return (new Step3approvalResource($clerical))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '5010', '', 510, errorDesc($e), 'Error occured in server side ');
        }
        
	}

	public function Step4ApprovalStatus(Request $request)
	{    

		$validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
			'is_final_approved'=>'required|numeric|gt:0|between:1,3',
            
        ], 
		);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30009', '', '502', '', $validator->messages());
        }

		try{
            $request-> merge(['user_id' => $request->eso_id]);
            $input=$request->except('eso_id');
            $clerical = Clerical::find($request->id);
			$clerical->	is_final_approved = $request->is_final_approved;
            $clerical->update($input);
			
			$franchise_user_name = $clerical->eso->name;
			$franchise_user_email = $clerical->eso->email;
			$data = array();
			$data['email'] = $franchise_user_email;
			$data['name'] = $franchise_user_name;
			$data['bodytext'] = '<p>Dear ' . $franchise_user_name . ',</p></br></br>
			<p>'.$clerical->full_name.' has completed the Step-3 of the Clerical application, Kindly review the same and take the necessary action.</p>';
			$view = 'mails.admin.commonmailbody';
			$subject = 'Clerical Job Application';
			// print_r($data);die;
	
			Mail::to($franchise_user_email)->send(new CommonMail($franchise_user_name, $view, $subject, $data));

			// admin
			$admin = Admin::first();
			$admin_name = $admin->name;
			$admin_email = $admin->email;
			$data = array();
			$data['email'] = $admin_email;
			$data['name'] = $admin_name;
			$data['bodytext'] = '<p>Dear Admin,</p></br></br>
			<p>'.$clerical->full_name.' has completed the Step-3 of the Clerical application, Kindly review the same and take the necessary action.</p>';
			$view = 'mails.admin.commonmailbody';
			$subject = 'Clerical Job Application';
	
			Mail::to($admin_email)->send(new CommonMail($admin_name, $view, $subject, $data));

			// Job applicant

		  
			$clerical_name = $clerical->full_name;
			$clerical_email = $clerical->job->email;
			$data = array();
			$data['email'] = $clerical_email;
			$data['name'] = $clerical_name;
			$data['bodytext'] = '<p>Dear ' . $clerical_name . ',</p></br></br>
			<p>You have completed step-3 of the application. We will review your application and get back to you shortly.</p>';
			$view = 'mails.admin.commonmailbody';
			$subject = 'Clerical Job Application';
	
			Mail::to($clerical_email)->send(new CommonMail($clerical_name, $view, $subject, $data));



			$metaData= metaData(true, $request, '5012', 'success', 512, '');
            return (new Step4ApprovalResource($clerical))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '5012', '', 512, errorDesc($e), 'Error occured in server side ');
        }
        
	}


	public function addclericalnote(Request $request)
    {	
		$validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
			'note'=>'required',
            
        ], 
		);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30009', '', '502', '', $validator->messages());
        }
		try{
			$clerical = new Clerical;
			$clerical->note = $request->note;
			$clerical->save();
			return metaData(true, $request, '4002', 'success', 200, '');
		}
		catch(\Exception $e){
			return metaData(false, $request, '5012', '', 512, errorDesc($e), 'Error occured in server side ');
		}
			

    }
}

