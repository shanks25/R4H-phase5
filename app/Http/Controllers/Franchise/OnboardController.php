<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\OnboardCollection;
use App\Models\TripMaster;
use App\Traits\TripTrait;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OnboardController extends Controller
{
    use TripTrait;
    public function index(Request $request) //20001
    {
        try {
            $validator = Validator::make($request->all(), [
                'onboard_status_id' => 'required|numeric',
                'payor_type' => 'numeric',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request, '2015', '', $error_code = 400, '', $validator->messages());
            }
            $dataCollection = $this->getTrips($request);
            // return $dataCollection;
            return  new OnboardCollection($dataCollection);
        } catch (\Exception $e) {
            return metaData(false, $request, 2015, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function getTrips($request)
    {
        $start_date = '';
        $end_date = '';
        $user_timezone = eso()->timezone;
        if ($request->filled('start_date')) {
            $start_date = $request->start_date;
        }
        if ($request->filled('end_date')) {
            $end_date = $request->end_date;
        }
        if ($start_date == '' && $end_date == '') {
            $today_date = timezoneCurrentDate($user_timezone)->format('Y-m-d');
            if ($request->onboard_status_id == 5) { //if expired then get yesterday trip 
                $tz_obj = new DateTimeZone($user_timezone);
                $yesterday =  new DateTime("yesterday", $tz_obj);
                $today_date =  $yesterday->format('Y-m-d');
            }
            $start_date = $today_date;
            $end_date = $today_date;
        }
        $request->merge(['start_date' => $start_date]);
        $request->merge(['end_date' => $end_date]);
        // return $request;
        $with_array = [
            'levelOfService:id,name',
            'member:id,name,member_phone_no',
            'payorTypeNames:id,name',
            'member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',
        ];
        $trips = $this->trips($request, $with_array);
        $total_price_sum = decimal2digitNumber($trips->sum('total_price'));
        $request->merge(['total_price_sum' => $total_price_sum]);
        return $trips->paginate(config('Settings.pagination'));
    }
    public function onboardData($request)
    {
        //read before edit 
        // onboard if start and end date is null then data should be todays date 
        //if onboard_status_id = 5(expired) then date bacomes yesterdays date 

        $start_date = '';
        $end_date = '';
        $members = array();
        $level_of_services = array();
        $payor_ids = array();
        $user_timezone = eso()->timezone;
        if ($request->filled('start_date')) {
            $start_date = searchStartDate($request->start_date, $user_timezone);
        }
        if ($request->filled('end_date')) {
            $end_date = searchEndDate($request->end_date, $user_timezone);
        }
        if ($start_date == '' && $end_date == '') {
            $today_date = timezoneCurrentDate($user_timezone)->format('Y-m-d');
            if ($request->onboard_status_id == 5) { //if expired then get yesterday trip 
                $tz_obj = new DateTimeZone($user_timezone);
                $yesterday =  new DateTime("yesterday", $tz_obj);
                $today_date =  $yesterday->format('Y-m-d');
            }
            $start_date = searchStartDate($today_date, $user_timezone);
            $end_date = searchEndDate($today_date, $user_timezone);
        }
        if ($request->filled('member_id')) {
            $members =  json_decode($request->member_id, true);
        }
        if ($request->filled('level_of_service_id')) {
            $level_of_services =  json_decode($request->level_of_service_id, true);
        }
        if ($request->filled('payor_id')) {
            $payor_ids =  json_decode($request->payor_id, true);
        }
        // DB::enableQueryLog();
        $trips = TripMaster::select('id', 'date_of_service', 'trip_no', 'pickup_address', 'drop_address', 'master_level_of_service_id', 'payor_type', 'payor_id', 'payable_type', 'Member_name', 'member_id', 'onboard_status', 'shedule_pickup_time', 'pickup_zip', 'drop_zip', 'county_type', 'trip_price', 'adjusted_price', 'total_price')
            ->with('levelOfService:id,name')
            ->with('member:id,name,member_phone_no')
            ->with('payorTypeNames:id,name')
            ->with('member:id,name,email,mobile_no,first_name,middle_name,last_name,gender')

            ->with('payor:id,name,phone_number');

        if ($start_date != '') {
            $trips =  $trips->whereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN trip_master_ut.shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) >="' . $start_date . '"');
        }
        if ($end_date != '') {
            $trips =  $trips->WhereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN trip_master_ut.shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) <="' . $end_date . '"');
        }
        if (count($members) > 0) {
            $trips =  $trips->whereIn('member_id', $members);
        }
        if (count($level_of_services) > 0) {
            $trips =  $trips->whereIn('master_level_of_service_id', $level_of_services);
        }
        if ($request->filled('payor_type') && $request->payor_type != 0) {
            $trips =  $trips->where('payor_type', $request->payor_type);
        }
        if (count($payor_ids) > 0) {
            $trips =  $trips->whereIn('payor_id', $payor_ids);
        }
        if ($request->filled('trip_id')) {
            $trips =  $trips->where('trip_no', $request->trip_id);
        }
        return $trips =  $trips->eso()
            ->where('onboard_status', $request->onboard_status_id)
            ->paginate(config('Settings.pagination'));
    }
    public function updateStatus(Request $request)
    {
        // return $request->onboard_status_id;
        try {
            $validator = Validator::make($request->all(), [
                'onboard_status_id' => 'required|numeric',
                'trip_ids' => 'required|array',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request, '2016', '', $error_code = 400, '', $validator->messages());
            }
            $vallid_status = [1, 2, 4]; // 1-accept,2-reject,4-hold 
            if (!in_array($request->onboard_status_id, $vallid_status)) {
                return   metaData(false, $request, '2016', '', $error_code = 400, '', 'Onboard status not vallid');
            }
            $trips = TripMaster::whereIn('id', $request->trip_ids)->update(['onboard_status' => $request->onboard_status_id]);
            $data_array = [
                "updateCount" => $trips
            ];
            $convert_array = ['data' => $data_array];
            return   merge($convert_array, metaData(true, $request, 2016, 'success', 200, '', ''));
        } catch (\Exception $e) {
            return metaData(false, $request, 2016, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
