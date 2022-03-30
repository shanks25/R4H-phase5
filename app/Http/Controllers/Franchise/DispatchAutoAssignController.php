<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Logics\DriverLogic;
use App\Logics\Logics;
use App\Models\DriverLevelofService;
use App\Models\DriverMaster;
use App\Models\LevelofServiceBufferTime;
use App\Models\MasterLevelOfService;
use App\Models\TripMaster;
use App\Traits\DriverTrait;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DispatchAutoAssignController extends Controller
{
    use DriverTrait;
    public function index(Request $request)
    {
        try {
            DB::beginTransaction();
            $collectAutoAssign = array();
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails()) {
                return metaData(false, $request, '2021', '', $error_code = 400, '', $validator->messages());
            }
            $checkDate = Logics::isDateValid($request->date);
            if (!$checkDate) {
                return metaData(false, $request, '2021', '', $error_code = 400, '', 'Invalid date');
            }
            // 1) Pool all active available drivers for the day. (with checking leave )
            // 2) Take the hardest level of service  (1st – Stretcher, 2nd – Wheelchair, 3rd – Ambulatory).
            //     2.1) first take all trips of level of service (Stretcher)
            //     2.2) first take all trips of level of service (Wheelchair)
            //     2.3) first take all trips of level of service (Ambulatory)
            // 3)take hardeat trip first 
            //     3.1) Out of County PU to Out of County DO (home address is baner pickup is aundh and drop is shivajinagar)
            //     3.2) Out of County PU to County DO (home address is baner pickup is aundh and drop is baner)
            //     3.3) County PU to Out of County DO (home address is baner pickup is baner and drop is aundh)
            //     3.4) Count PU to County DO (home address is baner pickup is baner and drop is baner)
            // 4) check driver is free for given trip time and driver level of service 
            // 5) check driver is eligible to arrive at pickup location (given time driver can arrive at pickup using google map estimated arrival time )
            //         4.1) consider level of service buffer time and add this time to google suggest time  (level of service buffer time which is ESO define for that particular level of service) 
            //         (if trip is first of the day then consider driver location is home if trip is not first then consider last drop off address)
            //          
            // 6) check driver utilization 
            //     6.1) which is higer utilization near 500 assign trip to that driver  

            // return $request;
            // level of service id = 3- stretcher ,  2 - wheel chair , 1- ambulatory 


            // return $level_of_service;
            $collectAutoAssign_3 = $this->autoAssignLogic([3], $request);
            if (count($collectAutoAssign_3) > 0) {
                array_push($collectAutoAssign, $collectAutoAssign_3);
            }
            $collectAutoAssign_2 = $this->autoAssignLogic([2], $request);
            if (count($collectAutoAssign_2) > 0) {
                array_push($collectAutoAssign, $collectAutoAssign_2);
            }
            $collectAutoAssign_1 = $this->autoAssignLogic([1], $request);
            if (count($collectAutoAssign_1) > 0) {
                array_push($collectAutoAssign, $collectAutoAssign_1);
            }
            // get remaining level of service 
            $level_of_service_remaining = MasterLevelOfService::select('id')
                ->where('id', '>', '3')
                ->orderBy('id', 'ASC')
                ->get()
                ->toArray();
            if (count($level_of_service_remaining) > 0) {
                $only_l_o_s_ids =  array_column($level_of_service_remaining, 'id');
                $collectAutoAssign_others = $this->autoAssignLogic($only_l_o_s_ids, $request);
                if (count($collectAutoAssign_others) > 0) {
                    array_push($collectAutoAssign, $collectAutoAssign_others);
                }
            }
            $trips = array_column($collectAutoAssign, 'driver_id', 'trip_id');
            $only_assign = array();
            if (count($collectAutoAssign) > 0) {
                foreach ($collectAutoAssign as $dtl1) {
                    foreach ($dtl1 as $dtl) {
                        // return $dtl[0]['trip_id'];
                        $data['trip_id'] = $dtl[0]['trip_id'];
                        $data['driver_id'] = $dtl[0]['driver_id'];
                        array_push($only_assign, $data);
                    }
                }
            }

            $todays_dispatch = DispatchController::index($request, 1, $only_assign);
            $dispatch['dispatch'] = $todays_dispatch;
            $dispatch['auto_assign'] = $only_assign;
            $returnData['data'] = $dispatch;
            // return count($todays_dispatch['drivers']);
            $dataMeta = [
                'meta' => [
                    'total' => count($todays_dispatch['drivers']), //count($todays_dispatch),
                    'total_auto_assign' => count($only_assign),
                ],
            ];
            $metaData = metaData(true, $request, '2021');
            $new_merge = merge($metaData, $dataMeta);
            return response()->json(merge($returnData, $new_merge));
            // return $returnData;
        } catch (\Exception $e) {
            return metaData(false, $request, 2021, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function autoAssignLogic($level_of_service_array, $request)
    {
        ////////////////////
        $collectAutoAssign = array();
        $timezone = eso()->timezone;
        // $tz_obj = new DateTimeZone($timezone);
        // $today = ;
        $today_date = $request->date;
        // 
        $start_date = searchStartDate($today_date, $timezone);
        $end_date = searchEndDate($today_date, $timezone);

        $trips = TripMaster::select('id', 'level_of_service', 'pickup_zip', 'drop_zip', 'date_of_service', 'trip_no', 'timezone', 'shedule_pickup_time', 'shedule_drop_time', 'master_level_of_service_id', 'pickup_address', 'drop_address', 'user_id')
            ->where('status_id', 2)
            ->where('user_id', $request->eso_id)
            ->where('leg_no', 1)
            ->whereIn('master_level_of_service_id', $level_of_service_array)
            ->whereNotNull('shedule_pickup_time')
            ->tripSearchStartEndDate($start_date, $end_date)
            ->orderBy('pickup_county_id', 'DESC')
            ->orderBy('drop_county_id', 'ASC')
            // ->orderBy(DB::raw('ISNULL(shedule_pickup_time), shedule_pickup_time'), 'ASC')
            ->get();
        //driver todays leave 
        $driver_leave_ids = DriverLogic::LeaveDriverIds($today_date);
        // return $trips; //count($trips);
        foreach ($trips as $tripData) {
            // return $tripData;
            $trip_level_of_service = trim($tripData->master_level_of_service_id);
            // return $trip_level_of_service;
            $drivers_level_service = DriverLevelofService::select('driver_id');
            if (count($driver_leave_ids) > 0) {
                $drivers_level_service = $drivers_level_service->whereNotIn('driver_id', $driver_leave_ids);
            }
            // return $trip_level_of_service;
            $drivers_level_service =  $drivers_level_service
                ->where('level_of_service_id', $trip_level_of_service)
                ->groupBy('driver_id')
                ->get()
                ->toArray();
            // return $drivers_level_service;
            if (count($drivers_level_service) > 0) {
                $driver_ids = array_column($drivers_level_service, 'driver_id');
                $driversMaster = DriverMaster::select('id')
                    ->whereIn('id', $driver_ids)
                    ->where('status', '1')
                    ->where('user_id', $request->eso_id)
                    ->where('vehicle_id', '!=', NULL)
                    ->get()
                    ->toArray();
                // return $driversMaster;
                if (count($driversMaster) > 0) {
                    $collect_driver_ids = array_column($driversMaster, 'id');
                    $collect_driver_ids = $this->checkDriverTimeAvailability($collect_driver_ids, $tripData, $start_date, $end_date);
                    if (count($collect_driver_ids) > 0) {
                        $final_driver_ids = TripMaster::select('driver_id', DB::raw('COALESCE(SUM(IFNULL(total_price,0)),0) as driver_utilization'))
                            ->whereIn('driver_id', $collect_driver_ids)
                            ->where('status_id', '!=', 2)
                            ->tripSearchStartEndDate($start_date, $end_date)
                            ->whereNotNull('driver_id')
                            ->groupBy('id')
                            ->get()->toArray();
                        if (count($final_driver_ids) > 0) {
                            $driver_utilizations = array_column($final_driver_ids, 'driver_utilization', 'driver_id');
                            $only_driver_ids = array_column($final_driver_ids, 'driver_id');
                            foreach ($collect_driver_ids as $ids) {
                                if (!in_array($ids, $only_driver_ids)) {
                                    $data_utilization['driver_id'] = $ids;
                                    $data_utilization['driver_utilization'] = 0;
                                    array_push($final_driver_ids, $data_utilization);
                                }
                            }
                            usort($final_driver_ids, function ($a, $b) {
                                if ($a['driver_utilization'] == $b['driver_utilization']) return 0;
                                return $a['driver_utilization'] < $b['driver_utilization'] ? 1 : -1;
                            });
                            // return $final_driver_ids;
                            foreach ($final_driver_ids as $final_ids) {
                                $assign_array = $this->finalAssign($tripData, $final_ids['driver_id'], $start_date, $end_date);
                                if (count($assign_array) > 0) {
                                    array_push($collectAutoAssign, $assign_array);
                                    // $collectAutoAssign[] = $assign_array;
                                    break;
                                }
                            }
                        } else { //all driver is 0 utilization 
                            $driver = DriverMaster::select('id', 'address_lat', 'address_lng')
                                ->whereIn('id', $collect_driver_ids)
                                ->orderBy('name', 'ASC')
                                ->first();
                            $assign_array = $this->finalAssign($tripData, $driver->id, $start_date, $end_date);
                            if (count($assign_array) > 0) {
                                // $collectAutoAssign[] = $assign_array;
                                array_push($collectAutoAssign, $assign_array);
                            }
                        }
                    }
                }
            }
        }
        return $collectAutoAssign;
    }
    public function checkDriverUtilization($driver_id, $start_date, $end_date)
    {
        /* The above code is checking if the driver has completed a trip within the last 10 hours. If
       they have, then they are not eligible to start a new trip. */

        $driver_time = TripMaster::select(DB::raw('COALESCE(SUM(IFNULL(estimated_trip_duration,0)),0) AS trip_duration'))
            ->where('driver_id', $driver_id)
            ->where('status_id', '!=', 2)
            // ->where('user_id', $user_id)
            ->tripSearchStartEndDate($start_date, $end_date)
            ->first();
        if ($driver_time->trip_duration < 36000) { // 10 hours in seconds 
            return 1; //applicable for new trip 
        } else {
            return 0;
        }
    }
    public function checkDriverTimeAvailability($driver_ids, $tripData)
    {
        /* This code is checking if the driver is available for the trip. */
        $collect_driver_ids = array();
        $collect_not_time_available_driver_ids = array();
        $startTime = date('Y-m-d', strtotime($tripData->date_of_service)) . ' ' . date('H:i:s', strtotime($tripData->shedule_pickup_time));
        $endTime = date('Y-m-d', strtotime($tripData->date_of_service)) . ' ' . date('H:i:s', strtotime($tripData->shedule_drop_time));
        // $startTime = date('H:i:s', strtotime('-15 minute', strtotime($startTime)));
        // $endTime = date('H:i:s', strtotime('+15 minute', strtotime($endTime)));
        foreach ($driver_ids as $ids) {
            $trip_date = TripMaster::select('id', 'date_of_service')
                ->where('date_of_service', $tripData->date_of_service)
                ->where('driver_id', $ids)
                ->whereIn('status_id', [1, 7, 4, 10, 11, 12])
                ->whereNotNull('shedule_pickup_time')
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('shedule_pickup_time', '>=', $startTime);
                    $query->where('shedule_pickup_time', '<=', $endTime);
                })
                ->orWhere(function ($query) use ($startTime, $endTime, $ids, $tripData) {
                    $query->where('date_of_service',  $tripData->date_of_service);
                    $query->where('driver_id', $ids);
                    $query->whereIn('status_id', [1, 7, 4, 10, 11, 12])
                        ->whereNotNull('shedule_pickup_time');
                    $query->where('shedule_drop_time', '>=', $startTime);
                    $query->where('shedule_drop_time', '<=', $endTime);
                })
                ->get();
            if (count($trip_date) > 0) {
                array_push($collect_not_time_available_driver_ids, $ids);
            } else {
                array_push($collect_driver_ids, $ids);
            }
        }
        return $collect_driver_ids;
    }
    public function checkDriverPickup($tripData, $driver_id, $start_date, $end_date)
    {
        /* The code is checking if the driver can arrive at the pickup location in the given
        time. */
        $startTime = date('Y-m-d', strtotime($tripData->date_of_service)) . ' ' . date('H:i:s', strtotime($tripData->shedule_pickup_time));
        // $endTime = date('Y-m-d', strtotime($tripData->date_of_service)) . ' ' . date('H:i:s', strtotime($tripData->shedule_drop_time));
        $driver_last_trip = TripMaster::select('drop_address', 'shedule_drop_time', 'id')
            ->tripSearchStartEndDate($start_date, $end_date)
            ->whereIn('status_id', [1, 3, 6, 7, 4, 10, 11, 12])
            ->where('driver_id', $driver_id)
            ->where('shedule_pickup_time', '<', $startTime)
            ->orderBy(DB::raw('ISNULL(shedule_pickup_time), shedule_pickup_time'), 'DESC')
            ->first();
        if ($driver_last_trip) {
            $durationDetails = Logics::getGoogleDirectionDuration($driver_last_trip->drop_address, $tripData->pickup_address);
            $driver_time_to_pickup = $durationDetails['totalduration'];
            $level_of_service_buffer_time = LevelofServiceBufferTime::where('level_of_service_id', $tripData->master_level_of_service_id)
                ->where('user_id', $tripData->user_id)
                ->first();
            $buffer_time_in_seconds = $level_of_service_buffer_time->buffer_time ?? 0; // buffer time seconds 
            $total_time = $buffer_time_in_seconds + $driver_time_to_pickup;

            $driver_arrive_time = date('H:i:s', strtotime('+' . $total_time . ' seconds', strtotime($driver_last_trip->shedule_drop_time)));
            if (strtotime($driver_last_trip->shedule_drop_time) < strtotime($driver_arrive_time)) {
                return 1; // driver can arrived at pick up in given time
            } else {
                return 0; // not arrived at pickup in given time 
            }
        } else {
            return 1;
        }
    }
    public function finalAssign($tripData, $driver_id, $start_date, $end_date)
    {
        $assign_trips_array = array();
        $total_assign_count = 0;
        $utilization = $this->checkDriverUtilization($driver_id, $start_date, $end_date);
        // return $utilization;
        if ($utilization == 1) {
            $pickupOnTime =  $this->checkDriverPickup($tripData, $driver_id, $start_date, $end_date);
            if ($pickupOnTime == 1) {
                $assign_flag = 1;
                $notification_flag = 0;
                $driver_id;
                $trip_assign_flag = $this->assignLogic($driver_id, $tripData->id, $assign_flag, $notification_flag);
                if ($trip_assign_flag) {
                    $total_assign_count++;
                    $current_trip_dtls['trip_id'] = $tripData->id;
                    $current_trip_dtls['driver_id'] = $driver_id;
                    array_push($assign_trips_array, $current_trip_dtls);
                    $check_another_leg = TripMaster::where('parent_id', $tripData->id)
                        ->where('trip_format', '!=', 3)
                        ->get();
                    if (count($check_another_leg) > 0) {
                        foreach ($check_another_leg as $leg) {
                            $total_assign_count++;
                            $trip_assign_flag = $this->assignLogic($driver_id, $leg->id, $assign_flag, $notification_flag);
                            $current_trip_dtls['trip_id'] = $leg->id;
                            $current_trip_dtls['driver_id'] = $driver_id;
                            array_push($assign_trips_array, $current_trip_dtls);
                        }
                    }
                }
            }
        }
        return $assign_trips_array;
    }
    public function acceptAutoAssign(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'auto_assign' => 'required|array',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request, '2022', '', $error_code = 400, '', $validator->messages());
            }

            $collectAutoAssign = array();
            $dtl_auto_assign = $request->auto_assign;
            $user_id = $request->eso_id;
            $only_trip_ids =  array_column($dtl_auto_assign, 'trip_id');
            $only_driver_ids =  array_column($dtl_auto_assign, 'driver_id');
            $only_driver_ids =  array_unique($only_driver_ids);

            $isVallidEso = Logics::isTripIdsVallidForEso($only_trip_ids, $user_id);
            $isValidDriverEso = Logics::isDriverIdsValid($only_driver_ids, $user_id);

            // return $isValidDriverEso;
            if (!$isVallidEso) {
                return   metaData(false, $request, '2022', '', $error_code = 400, '', 'Eso not vallid for trips');
            }
            if (!$isValidDriverEso) {
                return   metaData(false, $request, '2022', '', $error_code = 400, '', 'Invalid driver_id');
            }
            /* This code is checking if the driver_id is valid or not. */
            foreach ($only_driver_ids as $did) {
                $isValidDriver = Logics::isDriverValid($did, $user_id);
                if (!$isValidDriver) {
                    return   metaData(false, $request, '2022', '', $error_code = 400, '', 'Invalid driver_id');
                }
            }
            /* This code is checking if the trip_id is valid or not. */
            foreach ($only_trip_ids as $tid) {
                $isValidTrip = Logics::isTripValid($tid, $user_id);
                if (!$isValidTrip) {
                    return   metaData(false, $request, '2022', '', $error_code = 400, '', 'Invalid trip_id');
                }
            }

            $assign_flag = 1;
            $notification_flag = 1;
            $total_assign_count = 0;
            foreach ($dtl_auto_assign as $assign) {
                $trips =  TripMaster::select('id')->where('id', $assign['trip_id'])->first();
                if ($trips) {
                    $trip_assign_flag = $this->assignLogic($assign['driver_id'], $assign['trip_id'], $assign_flag, $notification_flag);
                    if ($trip_assign_flag) {
                        $total_assign_count++;
                    }
                    array_push($collectAutoAssign, $assign);
                }
            }
            DB::commit();
            $dataReturn['updateCount'] = $total_assign_count;
            $dataReturn['auto_assign'] = $collectAutoAssign;

            $data['data'] = $dataReturn;
            $metaData = metaData(true, $request, '2022');
            $new_merge = merge($data, $metaData);
            return $new_merge;
        } catch (\Exception $e) {
            return metaData(false, $request, 2022, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
