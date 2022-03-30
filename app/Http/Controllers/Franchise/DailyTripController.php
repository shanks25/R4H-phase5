<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\DailyTripLogCollection;
use App\Traits\TripTrait;
use App\Models\TripMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use DB;
use App\Models\MasterLevelofService;
use App\Models\Triplevelofservice;
use App\Models\Category;

class DailyTripController extends Controller
{
    use TripTrait;
    public function index(Request $request) 
    {
        // return 1;
        try {
            $user_timezone_offset = Carbon::now()->timezone(eso()->timezone)->getOffsetString();
            $app_timezone_offset  = Carbon::now()->getOffsetString();
    
            $trips =	DB::table('trip_master_ut as tr');
            $trips->leftjoin("trip_logs as tl", function ($join) {
                $join->on("tl.trip_id", "=", "tr.id")
                    ->on("tl.driver_id", "=", "tr.Driver_id");
            });
            // leftjoin('trip_logs as tl', 'tl.trip_id', '=', 'tr.id');
            $trips->select(
                DB::raw('DATE(CONVERT_TZ(concat(tr.date_of_service," ", CASE WHEN tr.shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END),"' . $app_timezone_offset . '","' . $user_timezone_offset . '")) as date'),
                DB::raw('count(*) as trip_count'),
                DB::raw('count(DISTINCT tr.other_driver_id) as other_drivers'),
                DB::raw('count(DISTINCT tr.Driver_id) as drivers'),
                DB::raw('count(DISTINCT tr.vehicle_id) as vehicles'),
                DB::raw('count(DISTINCT tr.member_id) as members'),
                DB::raw('sum(tr.additional_passengers) as total_additional_passengers'),
                DB::raw('sum(tr.driver_pay) as driver_payout'),
                DB::raw('sum(tr.insurance_amount) as total_insurace'),
                DB::raw('sum(tr.trip_profit) as trip_profit'),
                DB::raw('sum(tr.total_price) as total_trip_price'),
                DB::raw('sum(tl.period2 + tl.period3) as total_time'),
                DB::raw('sum(tl.period2_miles + tl.period3_miles) as total_miles'),
                DB::raw('SUM(TIME_TO_SEC(IFNULL(tl.period2,0)) + TIME_TO_SEC(IFNULL(tl.period3,0)) + IF(tr.trip_format = "Yes", IFNULL(tl.wait_time_sec,0), 0)) as time'),
                // DB::raw('sum(TIME_TO_SEC(tl.period2) + TIME_TO_SEC(tl.period3)) as time')
                'tr.id',
            );
    
    
            $trips->where('tr.user_id',$request->eso_id);
            $trips->whereIn('tr.status_id', [3, 5, 6]);
            if ($request->filled('start_date')) {
                $start_date = Carbon::parse($request->start_date, eso()->timezone)
                    ->startOfDay()
                    ->setTimezone(config('app.timezone'));
                // $trips->where('tr.date_of_service', '>=', $date);
                $trips->whereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) >="' . $start_date . '"');
            }
    
            if ($request->filled('end_date')) {
                $end_date = Carbon::parse($request->end_date, eso()->timezone)
                    ->endOfDay()
                    ->setTimezone(config('app.timezone'));
                // $trips->where('tr.date_of_service', '<=', $end_date);
                $trips->WhereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) <="' . $end_date . '"');
            }
    
    
    
            if ($request->filled('driver_id')) {
                $trips->where('tr.Driver_id', $request->driver_id);
            }
    
            if ($request->filled('vehicle_id')) {
                $trips->where('tr.vehicle_id', $request->vehicle_id);
            }
            	$trips =	$trips->groupBy('date')->orderBy('date', 'desc')->get();
                // ->paginate(config('Settings.pagination'));
        // return $trips;
        return new DailyTripLogCollection($trips);
    } catch (\Exception $e) {
        return metaData(false, $request, '4032', 502, errorDesc($e), 'Error occured in server side ');
    }
    }
}
