<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Exports\FairmeticExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\InsurancePeriodLog;
use App\Traits\TripTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
class FairmaticLogsController extends Controller
{
   
    use TripTrait;
    public function index(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'start'       =>  'required|date|date_format:Y-m-d',
            'end'      =>  'required|date|date_format:Y-m-d|after:start'
            
        ]);

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '4016', '', '502', '', $validator->messages());
        }
        try {
            $start_str_to_time = strtotime($request->start . '00:00:00') * 1000;
            $end_str_to_time = strtotime($request->end . '00:00:00') * 1000;

            $user_id = eso()->id;
            $res = InsurancePeriodLog::select('DriverId', 'Period', 'TripStartTimestamp', 'TripEndTimestamp')->where('TripStartTimestamp', '>=', $start_str_to_time)->where('TripEndTimestamp', '<=', $end_str_to_time)->whereIn('Period', ['P2', 'P3'])->where('user_id', $user_id)->get();
            
            $csv_name = 'insurance_report_' . $start_str_to_time . '_' . $end_str_to_time;
            
            Excel::store(new FairmeticExport($res), 'public/export_fairmetic/' .$csv_name . '.csv', 'local');
            $url = asset('storage/export_fairmetic/'. $csv_name . '.csv');
            $data['url'] = $url;
            $main['data'] = $data;
            return   merge($main, metaData(true, $request, 4016, 'success', 200, '', ''));
        } catch (\Exception $e) {
            return metaData(false, $request, 4016, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
        