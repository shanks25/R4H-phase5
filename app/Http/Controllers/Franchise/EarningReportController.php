<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\EarningCollection;
use App\Traits\TripTrait;
use App\Models\TripMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\MasterLevelofService;
use App\Models\Triplevelofservice;
use App\Models\Category;

class EarningReportController extends Controller
{
    use TripTrait;
    public function index(Request $request) 
    {
        try {
        $user_id = $request->eso_id;
        $user_timezone_offset = Carbon::now()->timezone(eso()->timezone)->getOffsetString();
        $app_timezone_offset  = Carbon::now()->getOffsetString();
        $trips = TripMaster::select('trip_master_ut.id',
            DB::raw('COUNT(*) as total_trips'),
            DB::raw('COUNT(DISTINCT trip_master_ut.Driver_id) as total_driver'),
            DB::raw('COUNT(DISTINCT trip_master_ut.vehicle_id) as total_vehicle'),
            DB::raw('COUNT(DISTINCT trip_master_ut.level_of_service) as total_level_of_service'),
            DB::raw('COUNT(DISTINCT trip_master_ut.pickup_zip) as total_pickup_zip'),
            DB::raw('ROUND(SUM(trip_master_ut.total_price),2) as total_revenue'),
            DB::raw('ROUND(SUM(trip_master_ut.driver_pay),2) as driver_pay'),
            DB::raw('ROUND(SUM(trip_master_ut.trip_profit),2) as trip_profit'),
            DB::raw('COUNT(DISTINCT trip_master_ut.status_id) as status_count'),
            DB::raw('SUM(IFNULL(trip_logs.period2_miles,0) + IFNULL(trip_logs.period3_miles,0)) as total_miles'),
            DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(IFNULL(trip_logs.period2,0)) + TIME_TO_SEC(IFNULL(trip_logs.period3,0)) + IF(trip_master_ut.trip_format = "Yes", IFNULL(trip_logs.wait_time_sec,0), 0))) as total_duration'),
            'driver_master_ut.name',
            'vehicle_master_ut.model_no',
            'vehicle_master_ut.VIN',
            DB::raw('DATE(CONVERT_TZ(concat(trip_master_ut.date_of_service," ", CASE WHEN trip_master_ut.shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END),"' . $app_timezone_offset . '","' . $user_timezone_offset . '")) as date')
        );
        // 'trip_master_ut.date_of_service');
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
        if ($request->filled('start_date')) {
            $start_date = Carbon::parse($request->start_date, auth()->user()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));

            $trips->whereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) >="' . $start_date . '"');
            // where('trip_master_ut.date_of_service', '>=', $date);
        }





        if ($request->filled('end_date')) {
            $end_date = Carbon::parse($request->end_date, auth()->user()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            $trips->WhereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) <="' . $end_date . '"');
            // ->where('trip_master_ut.date_of_service', '<=', $end_date);
        }
        // driver filter
        $total_driver = 0;
        if (isset($request->drivtype) && $request->drivtype != null) {
            $trips = $trips->whereIn('trip_master_ut.Driver_id', $request->drivtype);
            $total_driver = count($request->drivtype);
        }
        // vehicle filter
        $total_vehicle = 0;
        if (isset($request->vehicle) && $request->vehicle != null) {
            $trips = $trips->whereIn('trip_master_ut.vehicle_id', $request->vehicle);
            $total_vehicle = count($request->vehicle);
        }


        if (isset($request->search_payor_category) && $request->search_payor_category != "" && $request->search_payor_type != "") {
            if (isset($request->search_payor_type) && $request->search_payor_type != "") {
                $payor_type = $request->search_payor_type;
            } else {
                $category = Category::where('id', $request->search_payor_category)->first();
                $payor_type = $category->payor_type;
            }

            if ($payor_type == 1) {
                $trips = $trips->join("member_category AS c", "c.member_id", '=', "payor_id", 'inner');
                $trips = $trips->where('c.category_id', $request->search_payor_category);
            }

            if ($payor_type == 3) {
                $trips = $trips->join("broker_category AS c", "c.broker_id", '=', "payor_id", 'inner');
                $trips = $trips->where('c.category_id', $request->search_payor_category);
            }

            if ($payor_type != 1 && $payor_type != 3 && $payor_type != '') {
                $trips = $trips->join("crm_category AS c", "c.crm_id", '=', "payor_id", 'inner');
                $trips = $trips->where('c.category_id', $request->search_payor_category);
            }
        }

        // level of service filter
        $total_l_of_s = 0;
        $l_of_service_name = '';
        if (isset($request->service) && $request->service != null) {
            $trip_l_service = Triplevelofservice::whereIn('master_id', $request->service)->get();
            $only_trip_service = $trip_l_service->pluck('name');
            $trips = $trips->whereIn('trip_master_ut.level_of_service', $only_trip_service);
            $total_l_of_s = count($request->service);
            if ($total_l_of_s == 1) {
                $master_l_of_s =  MasterLevelofService::where('id', $request->service[0])->first();
                $l_of_service_name =  $master_l_of_s->name;
            }
        }
        //zip codes
        $total_zip = 0;
        $zip_name = '';
        if (isset($request->zipcode) && $request->zipcode != null) {
            $trips = $trips->whereIn('trip_master_ut.pickup_zip', $request->zipcode);
            $total_zip = count($request->zipcode);
            if ($total_zip == 1) {
                $zip_name = $request->zipcode[0];
            }
        }
        //day
        if (isset($request->day) && $request->day != null) {
            $trips = $trips->whereIn(DB::raw('weekday(trip_master_ut.date_of_service)'), $request->day); //
        }

        // payor type filter
        if (isset($request->search_payor_type) && $request->search_payor_type != "") {
            $trips->where(function ($query) use ($request) {
                $payor_array =  $request->search_payor_type; //
                //foreach ($payor_array as $key => $type) {
                // return $type;
                //if ($key == 0) {
                $query->Where('trip_master_ut.payor_type', $payor_array);
                if ($payor_array == 1 && isset($request->search_member_name) && count($request->search_member_name)) {
                    $query->whereIn('trip_master_ut.payor_id', $request->search_member_name);
                } elseif ($payor_array != 1 && $payor_array != 3 && $payor_array != '' && isset($request->search_facility_name) && count($request->search_facility_name)) {
                    $query->whereIn('trip_master_ut.payor_id', $request->search_facility_name);
                } elseif ($payor_array == 3 && isset($request->provtype) && count($request->provtype)) {
                    $query->whereIn('trip_master_ut.payor_id', $request->provtype);
                }
               
            });
        }
        // status filter
        if (isset($request->status_types) && $request->status_types != null) {
            $trips = $trips->whereIn('trip_master_ut.status_id', $request->status_types);
        } else {
            $trips = $trips->whereIn('trip_master_ut.status_id', [3, 5, 6]);
        }
        $trips = $trips->groupBy('date')
            ->orderBy('date', 'DESC')
            ->orderBy('trip_master_ut.id', 'DESC')
            ->where('trip_master_ut.user_id', $user_id);

        
        
        $trips = $trips->paginate(config('Settings.pagination'));
        // return $trips;
        return new EarningCollection($trips);
    } catch (\Exception $e) {
        return metaData(false, $request, '4032', 502, errorDesc($e), 'Error occured in server side ');
    }
    }

   
    public function earningReportTrips(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'current_date' => 'required|date_format:Y-m-d',
            
        ], );

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '4041', '', '502', '', $validator->messages());
        }

        try {
            return $this->earningReportTripsCollection($request);
        } catch (\Exception $e) {
            return metaData(false, $request, 4041, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
