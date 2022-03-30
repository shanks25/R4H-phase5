<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverUtiliztionCollection;
use App\Traits\TripTrait;
use App\Models\TripMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use DB;
use Illuminate\Support\Facades\Validator;
class DriverUtilzationController extends Controller
{
    use TripTrait;
    public function index(Request $request) 
    {
        try {
        $user_id = $request->eso_id;
        $user_timezone_offset = Carbon::now()->timezone(eso()->timezone)->getOffsetString();
        $app_timezone_offset  = Carbon::now()->getOffsetString();
        $trips = TripMaster::select(
            DB::raw('COUNT(*) as total_trips_'),
			DB::raw('COUNT(DISTINCT trip_master_ut.id) as total_trips'),
            DB::raw('COUNT(DISTINCT trip_master_ut.Driver_id) as total_driver'),
            DB::raw('COUNT(DISTINCT trip_master_ut.vehicle_id) as total_vehicle'),
            DB::raw('ROUND(SUM(trip_master_ut.total_price),2) as total_revenue'),
            DB::raw('ROUND(SUM(trip_master_ut.driver_pay),2) as driver_pay'),
            DB::raw('ROUND(SUM(trip_master_ut.trip_profit),2) as trip_profit'),
            DB::raw('SUM(IFNULL(trip_logs.period2_miles,0)) as total_period2_miles'),
			DB::raw('SUM(IFNULL(trip_logs.period3_miles,0)) as total_period3_miles'),
			DB::raw('SUM(IFNULL(trip_master_ut.total_price,0)) as total_trip_cost'),
			DB::raw('SUM(IFNULL(trip_master_ut.insurance_amount,0)) as total_insurance_cost'),

            DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(IFNULL(trip_logs.period2,0)) + TIME_TO_SEC(IFNULL(period3,0)) + IF(trip_master_ut.trip_format = "Yes", IFNULL(trip_logs.wait_time_sec,0), 0))) as total_duration'),


			DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(IFNULL(trip_logs.period2,0)) + IF(trip_master_ut.trip_format = "Yes", IFNULL(trip_logs.wait_time_sec,0), 0))) as total_p2_duration'),

			DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(IFNULL(trip_logs.period3,0)))) as total_p3_duration'),


			DB::raw('SEC_TO_TIME(TIME_TO_SEC(IFNULL(driver_utilization_detail.out_time,0)) - TIME_TO_SEC(IFNULL(driver_utilization_detail.in_time,0))) as total_driver_clock_duration'),

			DB::raw('SEC_TO_TIME(TIME_TO_SEC(IFNULL(driver_utilization_detail.out_time,0)) - TIME_TO_SEC(IFNULL(driver_utilization_detail.in_time,0)) -  SUM(TIME_TO_SEC(IFNULL(trip_logs.period2,0)) + TIME_TO_SEC(IFNULL(period3,0)) + IF(trip_master_ut.trip_format = "Yes", IFNULL(trip_logs.wait_time_sec,0), 0))) as downtime_duration'),

            'driver_master_ut.name','driver_master_ut.driver_type','driver_master_ut.id','driver_master_ut.insurance_type',
            'vehicle_master_ut.model_no','vehicle_master_ut.VIN',
			'driver_utilization_detail.in_time', 'driver_utilization_detail.out_time',
			'trip_master_ut.Driver_id', 'trip_master_ut.vehicle_id',
            DB::raw('DATE(CONVERT_TZ(concat(trip_master_ut.date_of_service," ", CASE WHEN trip_master_ut.shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END),"' . $app_timezone_offset . '","' . $user_timezone_offset . '")) as date'),
        );
        // 'trip_master_ut.date_of_service');
        $trips = $trips->leftJoin('trip_logs', function ($join) {
            $join->on('trip_master_ut.id', '=', 'trip_logs.trip_id')
                ->on('trip_master_ut.Driver_id', '=', 'trip_logs.driver_id');
        });
        $trips = $trips->leftJoin('driver_master_ut', function ($join) {
            $join->on('trip_master_ut.Driver_id', '=', 'driver_master_ut.id');
        });

		$trips = $trips->join('driver_utilization_detail', function ($join) {
            $join->on('driver_utilization_detail.driver_id', '=', 'trip_master_ut.Driver_id')
			->on('driver_utilization_detail.utilization_date', '=', 'trip_master_ut.date_of_service');
        });

        $trips = $trips->leftJoin('vehicle_master_ut', function ($join) {
            $join->on('trip_master_ut.vehicle_id', '=', 'vehicle_master_ut.id');
        });
        if ($request->filled('start_date')) {
            $start_date = Carbon::parse($request->start_date, auth()->user()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));

            $trips->whereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) >="' . $start_date . '"');
            // where('trip_master_ut.date_of_service', '>=', $date);
        }

		$groupcond = array();
		if (isset($request->drivtype) && $request->drivtype != "") { 
			$drivtype = $request->drivtype;

			if (in_array('0', $request->drivtype)) {
				$trips->WhereRaw('(trip_master_ut.Driver_id IN(' . implode(',', $drivtype) . ') or trip_master_ut.Driver_id is NULL) and (trip_logs.driver_id IN(' . implode(',', $drivtype) . ') or trip_logs.driver_id is NULL)');
			} else {
				$trips->WhereRaw('trip_master_ut.Driver_id IN(' . implode(',', $drivtype) . ') and (trip_logs.driver_id IN(' . implode(',', $drivtype) . ') or trip_logs.driver_id is NULL)');
			}
		}


		$filter_insurance_type = 0;
		if (isset($request->insurance_type) && $request->insurance_type != "") {
			$trips->where("driver_master_ut.insurance_type", $request->insurance_type);
			$filter_insurance_type = $request->insurance_type;
		}

		//$where_in_cond["tr.status_id"] = array('3', '5', '6');

		//$cond['tr.user_id'] = Auth::id();

		$trips->whereIn("trip_master_ut.status_id", array('3', '5', '6'));
		$trips->where("trip_master_ut.user_id", $user_id);

        if ($request->filled('end_date')) {
            $end_date = Carbon::parse($request->end_date, auth()->user()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            $trips->WhereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) <="' . $end_date . '"');
            // ->where('trip_master_ut.date_of_service', '<=', $end_date);
        }

        // vehicle filter 
        $total_vehicle = 0;
        if (isset($request->vehicle_name) && $request->vehicle_name != null) {
           	$trips = $trips->whereIn('trip_master_ut.vehicle_id', $request->vehicle_name);
        }

        $trips = $trips->groupBy('date', 'trip_master_ut.driver_id')
            ->orderBy('driver_master_ut.name', 'asc')
            ->orderBy('trip_master_ut.date_of_service', 'DESC');
            //->where('trip_master_ut.user_id', $user_id)
			//->whereIn("trip_master_ut.status_id", array('3', '5', '6'));
			//->where("driver_utilization_detail.utilization_date", 'trip_master_ut.date_of_service');

       
        // DB::enableQueryLog();
        $trips = $trips->paginate(config('Settings.pagination'));
        return new DriverUtiliztionCollection($trips);
    } catch (\Exception $e) {
        return metaData(false, $request, '4032', 502, errorDesc($e), 'Error occured in server side ');
    }
    }
    public function driverReportTrips(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'driver_id' => 'required',
            'vehicle_id' => 'required',
            
        ], );

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '4041', '', '502', '', $validator->messages());
        }

        try {
            return $this->driverReportTripsCollection($request);
        } catch (\Exception $e) {
            return metaData(false, $request, 4041, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
