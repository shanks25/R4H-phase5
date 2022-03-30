<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestoreTripCollection;
use App\Http\Resources\RouteProfatiabilityCollection;
use App\Traits\TripTrait;
use App\Models\TripMaster;
use App\Http\Requests\CommonFilterRequest;
use App\Models\StatusMaster;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use DB;

class RouteProfitabilityReportController extends Controller
{
    use TripTrait;
    public function index(Request $request) 
    {
        try {
        $user_id = $request->eso_id;
        $user_timezone_offset = Carbon::now()->timezone(eso()->timezone)->getOffsetString();
        $app_timezone_offset  = Carbon::now()->getOffsetString();
        $trips = TripMaster::select(
			DB::raw('COUNT(DISTINCT trip_master_ut.id) as total_trips'),
            DB::raw('ROUND(SUM(trip_master_ut.total_price),2) as total_revenue'),
            DB::raw('ROUND(SUM(trip_master_ut.driver_pay),2) as driver_pay'),
            DB::raw('ROUND(SUM(trip_master_ut.trip_profit),2) as trip_profit'),
            DB::raw('SUM(IFNULL(trip_logs.period2_miles,0)) as total_period2_miles'),
			DB::raw('SUM(IFNULL(trip_logs.period3_miles,0)) as total_period3_miles'),
			DB::raw('SUM(IFNULL(trip_master_ut.total_price,0)) as total_trip_cost'),
			DB::raw('SUM(IFNULL(trip_master_ut.insurance_amount,0)) as total_insurance_cost'),
			DB::raw('SUM(IFNULL(trip_master_ut.trip_profit,0)) as total_trip_profit'),

            DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(IFNULL(trip_logs.period2,0)) + TIME_TO_SEC(IFNULL(period3,0)) + IF(trip_master_ut.trip_format = "Yes", IFNULL(trip_logs.wait_time_sec,0), 0))) as total_duration'),

            'driver_master_ut.name','trip_master_ut.id','driver_master_ut.driver_type','driver_master_ut.insurance_type',
            'vehicle_master_ut.model_no','vehicle_master_ut.VIN',
			'trip_master_ut.Driver_id', 'trip_master_ut.vehicle_id', 'trip_master_ut.date_of_service', 'trip_master_ut.pickup_zip', 'trip_master_ut.drop_zip',
            DB::raw('DATE(CONVERT_TZ(concat(trip_master_ut.date_of_service," ", CASE WHEN trip_master_ut.shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END),"' . $app_timezone_offset . '","' . $user_timezone_offset . '")) as date'),
        );
        $trips = $trips->leftJoin('trip_logs', function ($join) {
            $join->on('trip_master_ut.id', '=', 'trip_logs.trip_id')
                ->on('trip_master_ut.Driver_id', '=', 'trip_logs.driver_id');
        });
        $trips = $trips->leftJoin('driver_master_ut', function ($join) {
            $join->on('trip_master_ut.Driver_id', '=', 'driver_master_ut.id');
        });

        $trips = $trips->leftJoin('vehicle_master_ut', function ($join) {
            $join->on('trip_master_ut.vehicle_id', '=', 'vehicle_master_ut.id');
        });
		

		if ($request->start_zipcode == "") {
			$start_zipcode = 'null';
		} else {
			$start_zipcode = $request->start_zipcode;
			$trips->where('trip_master_ut.pickup_zip', $start_zipcode);
		}

		if ($request->end_zipcode == "") {
			$end_zipcode = 'null';
		} else {
			$end_zipcode = $request->end_zipcode;
			$trips->where('trip_master_ut.drop_zip', $end_zipcode);
		}

        if ($request->filled('start_date')) {
            $start_date = Carbon::parse($request->start_date, auth()->user()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));

            $trips->whereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) >="' . $start_date . '"');
        }

        if ($request->filled('end_date')) {
            $end_date = Carbon::parse($request->end_date, auth()->user()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            $trips->WhereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) <="' . $end_date . '"');
        }

        $trips = $trips->groupBy('trip_master_ut.pickup_zip', 'trip_master_ut.drop_zip', 'trip_master_ut.id')
            ->orderBy('trip_master_ut.date_of_service', 'DESC')
            ->where('trip_master_ut.user_id', $user_id)
			->whereIn("trip_master_ut.status_id", array('3', '5', '6'));

        
        $trips = $trips->paginate(config('Settings.pagination'));
        // return $trips;
        return new RouteProfatiabilityCollection($trips);
    } catch (\Exception $e) {
        return metaData(false, $request, '4032', 502, errorDesc($e), 'Error occured in server side ');
    }
    }

    public function routeReportTrips(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'start_zip' => 'required|numeric|digits_between:5,6',
            'end_zip' => 'required|numeric|digits_between:5,6',
            
        ], );

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '4041', '', '502', '', $validator->messages());
        }

        try {
            return $this->routeReportTripsTripsCollection($request);
        } catch (\Exception $e) {
            return metaData(false, $request, 4041, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
