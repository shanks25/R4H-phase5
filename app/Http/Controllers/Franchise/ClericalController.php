<?php

namespace App\Http\Controllers\Franchise;

use DB;
use Carbon\Carbon;
use App\Models\Clerical;
use App\Models\Job;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClericalResource;
use App\Http\Resources\ClericalCollection;

use Illuminate\Http\Request;


class ClericalController extends Controller
{
    public function getList(Request $request)
    {
        $glob_page = $request->glob_page;

        $like_array = array();
                               

        $clerical = Clerical::with('job')->select('id', 'created_at', 'is_step3_approved', 'is_final_approved', 'home_telephone', 'address', 'job_id', 'step1_status', 'step2_status', 'step3_status', 'note', 'step4Point1_status', 'step1_point3_status', 'step4Point3_status', 'step3_approve_reject_status_remark', 'overall_status', 'first_name', 'middle_name', 'last_name', 'final_approve_reject_status_remark')->where('user_id',$request->eso_id)->latest();
        $clerical= Clerical::filterclericalList($request->all(), $clerical);

        
        $clerical = $clerical->get();
        return new ClericalCollection($clerical);
    }
}
