<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\DispatchLiveDriverTipsCollection;
use App\Logics\Logics;
use App\Models\DriverMaster;
use App\Models\TripMaster;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DispatchRouteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'driver_id' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return metaData(false, $request, '2025', '', $error_code = 400, '', $validator->messages());
            }
            $timezone = eso()->timezone;
            $today_date =  getTodayDate();
            $start_date = searchStartDate($today_date, $timezone);
            $end_date = searchEndDate($today_date, $timezone);
            $driver = Logics::isDriverValid($request->driver_id, $request->eso_id);

            if (!$driver) {
                return metaData(false, $request, '2025', '', $error_code = 400, '', 'Invalid driver_id');
            }
            // $driver = 
            $driver_id = $request->driver_id;
            $statusNotIn = [2, 8, 13]; //2 unassigned,8 expired,13 cancelled by eso 
            $drivers =  DriverMaster::select('id', 'name', 'lat', 'lng', 'last_update', 'status')
                ->where('id', $driver_id)
                ->first();
            $trips = TripMaster::select(
                'id',
                'driver_id',
                'master_level_of_service_id',
                'timezone',
                'shedule_pickup_time',
                'date_of_service',
                'trip_no',
                'trip_format',
                'estimated_trip_duration',
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
                'drop_lat',
                'drop_lng'
            )
                ->with('status:id,status_description')
                // ->with('driver:id,name,lat,lng,last_update')
                ->with(['statusPickupTime' => function ($query) {
                    $query->where('status', 4);
                }])
                ->with(['statusDropTime' => function ($query) {
                    $query->where('status', 9);
                }])
                ->with('member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',)
                ->where('driver_id', $driver_id)
                ->whereNotIn('status_id', $statusNotIn)
                ->tripSearchStartEndDate($start_date, $end_date)
                ->orderBy(DB::raw('ISNULL(shedule_pickup_time), shedule_pickup_time'), 'ASC')
                ->get();

            $trips = $trips->map(function ($t) {
                $t->date_of_service = modifyTripDate($t->date_of_service, $t->shedule_pickup_time);
                $t->shedule_pickup_time = modifyTripTime($t->date_of_service, $t->shedule_pickup_time);
                // check if trip is complete 
                if ($t->status_id == 3) { //trip status complete then get actual timing 
                    // pickup actual
                    $pickup_time = '';
                    $dropoff_time = '';
                    $tripTime1 = $t->status_pickup_time;
                    if (!empty($tripTime1)) {
                        if ($tripTime1->date_time != '') {
                            $pickup_time = modifyDriverLogTime($tripTime1->date_time, $tripTime1->timezone)->toTimeString();
                        }
                    }
                    // drop actual 
                    $tripTime2 = $t->status_drop_time; //getTripTime($assign->id, $assign->Driver_id, 9);
                    if (!empty($tripTime2)) {
                        if ($tripTime2->date_time != '') {
                            $dropoff_time = modifyDriverLogTime($tripTime2->date_time, $tripTime2->timezone)->toTimeString(); //date("H:i:s", strtotime($tripTime2[0]["date_time"]));
                        }
                    }
                    if ($pickup_time != '' && $dropoff_time != '') {
                        $time1 = new DateTime($t->date_of_service . ' ' . $pickup_time);
                        $time2 = new DateTime($t->date_of_service . ' ' . $dropoff_time);
                        $timediff = $time2->getTimestamp() - $time1->getTimestamp();
                        $duration =  $timediff; //$timediff->format('%i');
                    } else {
                        $duration = 0;
                    }
                    $t->estimated_trip_duration = $duration;
                }
                return $t;
            });
            // driver utilization 
            $driver_seconds = $trips->sum('estimated_trip_duration');
            $driver_mins = round($driver_seconds / 60);
            //utilization percent 
            $drivers->percent_utilized =  Logics::DriverUtilization($driver_seconds);
            if ($drivers->last_update) {
                $drivers->last_update = modifyTripWithDateTime($drivers->last_update)->format('Y-m-d H:i:s');
            }
            // create 2 array 1 for trip and 1 for driver details 
            $custom['trips'] = $trips;
            $custom['driver'] = $drivers;
            $returnData['data'] = $custom;

            $dataMeta = [
                'meta' => [
                    'total' => $trips->count()
                ],
            ];
            $metaData = metaData(true, $request, '2025');
            $new_merge = merge($metaData, $dataMeta);
            return response()->json(merge($returnData, $new_merge));
            // return $trips;
            // return new DispatchLiveDriverTipsCollection($trips);
        } catch (\Exception $e) {
            return metaData(false, $request, 2025, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
