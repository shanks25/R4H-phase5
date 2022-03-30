<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\DispatchTripsCollection;
use App\Logics\DriverLogic;
use App\Logics\Logics;
use App\Models\DriverMaster;
use App\Models\DriverZones;
use App\Models\StatusMaster;
use App\Models\TripMaster;
use App\Models\VehicleLevelofService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DispatchController extends Controller
{
    public function index(Request $request, $AutoAssignFlag = 0, $autoAssignTrips = array())
    {
        // return 3;
        /* The above code is used to get the driver assignment details. */
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request, '2019', '', $error_code = 400, '', $validator->messages());
            }
            $today_date = $request->date;
            $today_date = date("Y-m-d", strtotime($today_date));
            $timezone = eso()->timezone;
            $status = array();
            if ($request->filled('status_id')) {
                $status =  json_decode($request->status_id, true);
            }
            $status_array = [1, 3, 4, 7, 10, 11, 12, 5, 6];
            if (count($status) > 0) {
                $status_array = $status;
            }
            ////////
            $auto_trip_ids = array();
            $auto_trip_driver = array();
            if ($AutoAssignFlag == 1 && count($autoAssignTrips) > 0) {
                $auto_trip_ids = array_column($autoAssignTrips, 'trip_id');
                $auto_driver_ids = array_column($autoAssignTrips, 'driver_id');
                $auto_trip_driver = array_column($autoAssignTrips, 'driver_id', 'trip_id');
            }
            /////////

            $start_date = searchStartDate($today_date, $timezone);
            $end_date = searchEndDate($today_date, $timezone);

            $driver_leave_ids = DriverLogic::LeaveDriverIds($today_date);
            // DB::enableQueryLog();
            $driversAssigns = DriverMaster::select('id', 'status', 'user_id', 'vehicle_id', 'name')
                ->with(['trips' => function ($query) use ($start_date, $end_date, $status_array, $request,  $auto_trip_ids) {
                    $query->select('id', 'driver_id', 'master_level_of_service_id', 'timezone', 'shedule_pickup_time', 'date_of_service', 'trip_no', 'trip_format', 'estimated_trip_duration', 'Member_name', 'member_phone_no', 'pickup_address', 'drop_address', 'shedule_drop_time', 'drop_of_time', 'member_id', 'level_of_service', 'Driver_id', 'vehicle_id', 'trip_price', 'status_id', 'payout_type', 'pickup_zip', 'drop_zip', 'payor_name', 'total_price', 'trip_format')
                        ->with(['statusPickupTime' => function ($query) {
                            $query->where('status', 4);
                        }])
                        ->with(['statusDropTime' => function ($query) {
                            $query->where('status', 9);
                        }])
                        ->with('countyPickupNames:id,county_name,zip')
                        ->with('countyDropNames:id,county_name,zip')
                        ->orderBy(DB::raw('ISNULL(shedule_pickup_time), shedule_pickup_time'), 'ASC')
                        ->whereIn('status_id', $status_array)
                        ->where('user_id', $request->eso_id)
                        ->tripSearchStartEndDate($start_date, $end_date)
                        ->orderBy(DB::raw('ISNULL(shedule_pickup_time), shedule_pickup_time'), 'ASC');
                    if (count($auto_trip_ids) > 0) {
                        $query = $query->orWhereIn('id', $auto_trip_ids);
                    }
                }])
                ->where('status', '1')
                ->where('user_id', $request->eso_id)
                ->where('vehicle_id', '!=', NULL);
            if (count($driver_leave_ids) > 0) { //driver leave id on date 
                $driversAssigns = $driversAssigns->whereNotIn('id', $driver_leave_ids);
            }
            $driversAssigns = $driversAssigns->where('vehicle_id', '!=', 0)
                ->orderBy('name', 'asc')
                ->get();
            $driversAssigns = $driversAssigns->filter(function ($item) use ($auto_trip_ids, $auto_trip_driver) {
                //one leg 
                $one_leg_trips =  $item->trips->filter(function ($itemTrips) {
                    if ($itemTrips->trip_format == 1) {
                        return $itemTrips;
                    }
                })->values();
                //return trips
                $driver_return_trips =  $item->trips->filter(function ($itemReturn) {
                    if ($itemReturn->trip_format == 2) {
                        return $itemReturn;
                    }
                })->values();
                //will call
                $driver_will_caLL =  $item->trips->filter(function ($itemWill) {
                    if ($itemWill->trip_format == 3) {
                        return $itemWill;
                    }
                })->values();
                //wait time trips 
                $driver_wait =  $item->trips->filter(function ($itemWait) {
                    if ($itemWait->trip_format == 4) {
                        return $itemWait;
                    }
                })->values();
                /////check auto assign trips 
                $driver_with_auto =  $item->trips->filter(function ($itemAuto) use ($auto_trip_ids, $auto_trip_driver) {
                    if (in_array($itemAuto->id, $auto_trip_ids)) {
                        $itemAuto->driver_id = $auto_trip_driver[$itemAuto->id];
                        $itemAuto->status_id = 1;
                    }
                    return $itemAuto;
                })->values();
                //collect driver duration

                $item->trips->filter(function ($itemDuration) {
                    if ($itemDuration->status_id == 3) { //trip status complete then get actual timing 
                        // pickup actual
                        $pickup_time = '';
                        $dropoff_time = '';
                        $tripTime1 = $itemDuration->status_pickup_time;
                        if (!empty($tripTime1)) {
                            if ($tripTime1->date_time != '') {
                                $pickup_time = modifyDriverLogTime($tripTime1->date_time, $tripTime1->timezone)->toTimeString();
                            }
                        }
                        // drop actual 
                        $tripTime2 = $itemDuration->status_drop_time; //getTripTime($assign->id, $assign->Driver_id, 9);
                        if (!empty($tripTime2)) {
                            if ($tripTime2->date_time != '') {
                                $dropoff_time = modifyDriverLogTime($tripTime2->date_time, $tripTime2->timezone)->toTimeString(); //date("H:i:s", strtotime($tripTime2[0]["date_time"]));
                            }
                        }
                        if ($pickup_time != '' && $dropoff_time != '') {
                            $time1 = new DateTime($itemDuration->date_of_service . ' ' . $pickup_time);
                            $time2 = new DateTime($itemDuration->date_of_service . ' ' . $dropoff_time);
                            $timediff = $time2->getTimestamp() - $time1->getTimestamp();
                            $duration =  $timediff; //$timediff->format('%i');
                        } else {
                            $duration = 0;
                        }
                        $itemDuration->estimated_trip_duration = $duration;
                    }
                    $itemDuration->date_of_service = modifyTripDate($itemDuration->date_of_service, $itemDuration->shedule_pickup_time);
                    $itemDuration->shedule_pickup_time = modifyTripTime($itemDuration->date_of_service, $itemDuration->shedule_pickup_time);
                    return $itemDuration;
                })->values();
                $driver_seconds = $item->trips->sum('estimated_trip_duration');
                $driver_mins = round($driver_seconds / 60);
                //driver utilization percent 
                $driver_percent_utilized =  Logics::DriverUtilization($driver_seconds);
                //vehicle utilization 
                $total_revenue = $item->trips->sum('total_price');
                $vehicle_utilization_percent = Logics::vehicleUtilization($total_revenue);

                $item->one_legs_trips = count($one_leg_trips);
                $item->return_trips = count($driver_return_trips);
                $item->will_call_trips = count($driver_will_caLL);
                $item->wait_trips = count($driver_wait);
                $item->total_revenue = $total_revenue;
                $item->driver_minutes = $driver_mins;
                $item->driver_utilized_percent = round($driver_percent_utilized);
                $item->vehicle_utilization_percent = round($vehicle_utilization_percent);

                return $item;
            })->values();
            //total minutes 
            $total_minutes = $driversAssigns->sum('driver_minutes');
            $total_revenue = $driversAssigns->sum('total_revenue');
            $total_all_driver_time = intdiv($total_minutes, 60) . ':' . ($total_minutes % 60);

            $total_vehicle_utilization_percent = $driversAssigns->sum('vehicle_utilization_percent');
            $total_driver_utilized_percent = $driversAssigns->sum('driver_utilized_percent');
            // check driver non zero and vehicle non zero 
            $driver_non_zero_count = 0;
            $vehicle_non_zero_count = 0;
            foreach ($driversAssigns as $driver) {
                if ($driver->driver_utilized_percent > 0) {
                    $driver_non_zero_count++;
                }
                if ($driver->vehicle_utilizatio_percent > 0) {
                    $vehicle_non_zero_count++;
                }
            }
            $average_vehicle_utilization_percent = 0;
            if ($vehicle_non_zero_count > 0) {
                $average_vehicle_utilization_percent = $total_vehicle_utilization_percent / $vehicle_non_zero_count;
            }
            $average_driver_utilized_percent = 0;
            if ($driver_non_zero_count > 0) {
                $average_driver_utilized_percent =  $total_driver_utilized_percent / $driver_non_zero_count;
            }
            $data['drivers'] = $driversAssigns;
            $data['total_time'] = $total_all_driver_time;
            $data['average_driver_utilized_percent'] = round($average_driver_utilized_percent);
            $data['total_revenue'] = $total_revenue;
            $data['average_vehicle_utilized_percent'] = round($average_vehicle_utilization_percent);
            $returnData['data'] = $data;
            if ($AutoAssignFlag == 1) {
                return $data;
            }
            $dataMeta = [
                'meta' => [
                    'total' => $driversAssigns->count()
                ],
            ];
            $metaData = metaData(true, $request, '2019');
            $new_merge = merge($metaData, $dataMeta);
            return response()->json(merge($returnData, $new_merge));
        } catch (\Exception $e) {
            return metaData(false, $request, 2019, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function tripListStatusWise(Request $request)
    {
        /* This is the code for the API to get the trips. */
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
                'status_id' => 'required|array',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request, '2017', '', $error_code = 400, '', $validator->messages());
            }
            // check status in valid 
            // return $request->status_id;
            $isStatusValid = Logics::isStatusArrayVallid($request->status_id);
            if (!$isStatusValid) {
                return   metaData(false, $request, '2017', '', $error_code = 400, '', 'status_id array not valid.');
            }
            $checkDate = Logics::isDateValid($request->date);
            if (!$checkDate) {
                return metaData(false, $request, '2021', '', $error_code = 400, '', 'Invalid date');
            }
            $user_timezone = eso()->timezone;
            $start_date = searchStartDate($request->date, $user_timezone);
            $end_date = searchEndDate($request->date, $user_timezone);
            $trips = TripMaster::select('id', 'trip_format', 'member_name', 'member_phone_no', 'trip_no', 'timezone', 'date_of_service', 'shedule_pickup_time', 'shedule_drop_time', 'estimated_trip_duration', 'pickup_address', 'drop_address', 'master_level_of_service_id', 'trip_price', 'pickup_zip', 'drop_zip')
                ->with('levelofservice:id,name')
                ->whereIn('status_id', $request->status_id)
                ->eso()
                ->tripSearchStartEndDate($start_date, $end_date)
                ->orderBy(DB::raw('ISNULL(shedule_pickup_time), shedule_pickup_time'), 'ASC')
                ->get();
            return new DispatchTripsCollection($trips);
        } catch (\Exception $e) {
            return metaData(false, $request, 2017, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function assignTrips(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
                'driver_id' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request, '2017', '', $error_code = 400, '', $validator->messages());
            }
            $idDriverValid = Logics::isDriverValid($request->driver_id, $request->eso_id);
            if (!$idDriverValid) {
                return   metaData(false, $request, '2017', '', $error_code = 400, '', 'Invalid driver_id');
            }
            $isDateValid = Logics::isDateValid($request->date);
            if (!$isDateValid) {
                return   metaData(false, $request, '2017', '', $error_code = 400, '', 'Invalid date');
            }
            $user_timezone = eso()->timezone;
            $start_date = searchStartDate($request->date, $user_timezone);
            $end_date = searchEndDate($request->date, $user_timezone);
            $trips = TripMaster::select('id', 'trip_format', 'member_name', 'member_phone_no', 'trip_no', 'timezone', 'date_of_service', 'shedule_pickup_time', 'shedule_drop_time', 'estimated_trip_duration', 'pickup_address', 'drop_address', 'master_level_of_service_id', 'trip_price', 'pickup_zip', 'drop_zip')
                ->with('levelofservice:id,name')
                ->where('status_id', 1)
                ->where('driver_id', $request->driver_id)
                ->eso()
                ->tripSearchStartEndDate($start_date, $end_date)
                ->orderBy(DB::raw('ISNULL(shedule_pickup_time), shedule_pickup_time'), 'ASC')
                ->get();
            return new DispatchTripsCollection($trips);
        } catch (\Exception $e) {
            return metaData(false, $request, 2017, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function driverDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // 'date' => 'required|date_format:Y-m-d',
                'driver_id' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request, '2020', '', $error_code = 400, '', $validator->messages());
            }

            $driver_id =  $request->driver_id;
            $isDriverValid =  Logics::isDriverValid($driver_id, $request->eso_id);
            if (!$isDriverValid) {
                return   metaData(false, $request, '2020', '', $error_code = 400, '', 'invalid driver_id');
            }
            $details = DriverMaster::select('id', 'name')
                ->with('zone.zones:id,name', 'zone.zones.zips:zone_id,zipcode')
                ->where('id', $driver_id)
                ->first();
            $returnData['data'] = $details;
            $metaData = metaData(true, $request, '2020');
            $new_merge = merge($returnData, $metaData);
            return response()->json($new_merge);
        } catch (\Exception $e) {
            return metaData(false, $request, 2020, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
