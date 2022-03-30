<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\DispatchLiveDriverTipsCollection;
use App\Logics\DriverLogic;
use App\Models\DriverMaster;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchLocationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $driversAssigns = $this->getLiveDriver($request);
            // change date of service user timezone wise 
            $driversAssigns = $driversAssigns->filter(function ($driverItem) {
                $driverItem->trips->filter(function ($item) {
                    $item->date_of_service = modifyTripDate($item->date_of_service, $item->shedule_pickup_time);
                    $item->shedule_pickup_time = modifyTripTime($item->date_of_service, $item->shedule_pickup_time);
                    return $item;
                })->values();
                return $driverItem;
            })->values();
            $returnData['data'] = $driversAssigns;

            $dataMeta = [
                'meta' => [
                    'total' => $driversAssigns->count()
                ],
            ];
            $metaData = metaData(true, $request, '2024');
            $new_merge = merge($metaData, $dataMeta);
            return response()->json(merge($returnData, $new_merge));
        } catch (\Exception $e) {
            return metaData(false, $request, 2024, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function getLiveDriver($request)
    {
        $timezone = eso()->timezone;
        $today_date =  getTodayDate();
        $start_date = searchStartDate($today_date, $timezone);
        $end_date = searchEndDate($today_date, $timezone);
        $status_array = [10, 11, 12]; //1 assigned, 7 reassigned, 10 trip started, 11 arrived at pickup, 12 member on board, 5 no show , 6 late cancellation 
        $driver_leave_ids = DriverLogic::LeaveDriverIds($today_date);
        $driversAssigns = DriverMaster::select('id', 'status', 'user_id', 'vehicle_id', 'name', 'lat', 'lng')
            ->with('vehicle:id,model_no,unit_no')
            ->with(['trips' => function ($query) use ($start_date, $end_date, $status_array, $request) {
                $query->select(
                    'id',
                    'driver_id',
                    'master_level_of_service_id',
                    'timezone',
                    'shedule_pickup_time',
                    'date_of_service',
                    'trip_no',
                    'trip_format',
                    'estimated_trip_duration',
                    'Member_name',
                    'member_phone_no',
                    'pickup_address',
                    'drop_address',
                    'shedule_drop_time',
                    'drop_of_time',
                    'member_id',
                    'level_of_service',
                    'vehicle_id',
                    'trip_price',
                    'status_id',
                    'payout_type',
                    'pickup_zip',
                    'drop_zip',
                    'payor_name',
                    'total_price',
                    'trip_format',
                    'pickup_lat',
                    'pickup_lng',
                )
                    ->with('countyPickupNames:id,county_name,zip')
                    ->with('countyDropNames:id,county_name,zip')
                    ->with('member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',)
                    ->orderBy(DB::raw('ISNULL(shedule_pickup_time), shedule_pickup_time'), 'ASC')
                    ->whereIn('status_id', $status_array)
                    ->where('user_id', $request->eso_id)
                    ->tripSearchStartEndDate($start_date, $end_date)
                    ->orderBy(DB::raw('ISNULL(shedule_pickup_time), shedule_pickup_time'), 'ASC');
            }])
            ->where('status', '1')
            ->where('user_id', $request->eso_id)
            ->where('vehicle_id', '!=', NULL);
        if (count($driver_leave_ids) > 0) { //driver leave id on date 
            $driversAssigns = $driversAssigns->whereNotIn('id', $driver_leave_ids);
        }
        return  $driversAssigns = $driversAssigns->where('vehicle_id', '!=', 0)
            ->orderBy('name', 'asc')
            ->get();
    }
}
