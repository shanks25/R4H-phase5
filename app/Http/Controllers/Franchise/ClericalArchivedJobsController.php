<?php

namespace App\Http\Controllers\Franchise;

use App\Mail\CommonMail;
use App\Models\Clerical;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ClericalArchivedCollection;

class ClericalArchivedJobsController extends Controller
{ 

	public function index(Request $request)
	{
		$count = Clerical::where('user_id',$request->eso_id)->archived()->select('id')->get()->count();
		return $count;
	}

	public function getList(Request $request)
	{
		$validator = Validator::make($request->all(), [
            'step3_approval_status_filter' => 'nullable|in:0,1,2,3',
            'final_approval_status_filter' => 'nullable|in:0,1,2,3',
            'start_date' => 'nullable|date_format:d-m-Y',
            'end_date' => 'nullable|date_format:d-m-Y|after_or_equal:start_date'
            
        ], [
            'driver_type.required' => 'Driver Type is required.',
            
            
        ]);
		if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30042', '', '502', '', $validator->messages());
        }

		$returndata =   Clerical::with('job')->select('id','created_at', 'is_step3_approved','is_final_approved', 'home_telephone','address','job_id','step1_status','step2_status','step3_status','note','step4Point1_status','step1_point3_status','step4Point3_status','step3_approve_reject_status_remark')->where('user_id', esoId())->archived()->latest();

		if ($request->step3_approval_status_filter != NULL) {
			$returndata = $returndata->where('is_step3_approved', $request->step3_approval_status_filter);
		} 

		if ($request->final_approval_status_filter != NULL) {
			$returndata = $returndata->where('is_final_approved', $request->final_approval_status_filter);
		} 

		if ($request->filled('start_date')) {
				
			$returndata->where('created_at', '>=', start_date($request->start_date));
		}
		

		if ($request->filled('end_date')) {
			
			$returndata->where('created_at', '<=', end_date($request->end_date));
		}
	
		$returndata = $returndata->get();
		
		return (new ClericalArchivedCollection($returndata));
		
	}




	public function getCurrentFinalStatus($current_status, $id)
	{
		if ($current_status == 3) {
			$approve_status = 'Pending';
		} else if ($current_status == 2) {
			$approve_status = 'Approved';
		} else {
			$approve_status = 'Rejected';
		}

		return $approve_status;
	}

	public function getCurrentStep3Status($current_status, $id)
	{
		if ($current_status == 3) {
			$approve_status = 'Pending';
		} else if ($current_status == 2) {
			$approve_status = 'Approved';
		} else {
			$approve_status = 'Rejected';
		}
		return $approve_status;

	}


}
