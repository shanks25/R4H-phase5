<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\LogActivityCollection;
use App\Logics\Logics;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LogActivityController extends Controller
{
    public function index(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'trip_id' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request,  '20009', '', $error_code = 403, '',  $validator->messages());
            }
            $isValidTrip = Logics::isTripValid($request->trip_id, $request->eso_id);
            if (!$isValidTrip) {
                return   metaData(false, $request, '20009', '', $error_code = 403, '', 'Invalid trip_id');
            }
            $logs = LogActivity::where('trip_id', $request->trip_id)
                ->OrderBy('created_at', 'ASC')
                ->where('user_id', eso()->id)
                ->get();
            return new LogActivityCollection($logs);
        } catch (\Exception $e) {
            return metaData(false, $request, 2013, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public static function addToLog($subject)
    {
        $log = [];
        $log['subject'] = $subject;
        $log['url'] = Request::fullUrl();
        $log['method'] = Request::method();
        $log['ip'] = Request::ip();
        $log['agent'] = Request::header('user-agent');
        $log['user_id'] = auth()->check() ? auth()->user()->id : 1;
        LogActivityModel::create($log);
    }
}
