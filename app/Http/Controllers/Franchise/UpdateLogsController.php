<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLogRequest;
use App\Http\Resources\StatusLogsResource;
use App\Logics\Logics;
use App\Models\RelInvoiceItem;
use App\Models\TimezoneMaster;
use App\Models\TripLog;
use App\Models\TripMaster;
use App\Models\TripPayoutDetail;
use App\Models\TripStatusLog;
use App\Models\Vehicle;
use App\Traits\TripTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UpdateLogsController extends Controller
{
    use TripTrait;
    public function edit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'trip_id' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request, '20009', '', $error_code = 400, '', $validator->messages());
            }
            $isValidTrip = Logics::isTripValid($request->trip_id, $request->eso_id);
            if (!$isValidTrip) {
                return   metaData(false, $request, '20009', '', $error_code = 400, '', 'Invalid trip_id');
            }
            $id = $request->trip_id;
            $trips = TripMaster::select('id', 'trip_no', 'driver_id', 'date_of_service', 'appointment_time', 'shedule_pickup_time', 'pickup_address', 'drop_address', 'trip_start_address', 'status_id', 'vehicle_id', 'adjusted_price', 'trip_price', 'total_price', 'trip_format', 'vehicle_id')
                ->with(
                    'log:id,trip_id,driver_id,period0,period1,period2,period3,trip_status,estimated_loaded_miles,estimated_unloaded_miles,estimated_trip_duration,period1_miles,period2_miles,period3_miles',
                    'driver:id,name',
                )
                ->where('id', $id)
                ->eso()
                ->first();
            if ($trips) {
                $trips->status_logs = $this->tripStatusLog($trips->id, $trips->driver_id);
                $trips->invoice_exist = 'No';
                $invoice = RelInvoiceItem::where('trip_id', $id)->whereNull('is_deleted')->first();
                if ($invoice) {
                    $trips->invoice_exist = 'Yes';
                }
                $trips->last_remitted_cost = RelInvoiceItem::where('trip_id', $id)->where('provider_remitances_status_id', '!=', '1')->whereNull('is_deleted')->sum('paid_amount');
                return  new StatusLogsResource($trips);
            } else {
                return metaData(false, $request, 2010, '', 502, '', 'Trip id not found');
            }
        } catch (\Exception $e) {
            return metaData(false, $request, 2010, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function update(UpdateLogRequest $request)
    {
        // return $this->testcal();
        $set_trip_driver = $request->driver_id;
        $move_status = $request->status_id;
        $trip_price = $request->trip_price;
        $adjusted_price = $request->adjusted_price;
        $total_price = $request->total_price;
        $vehicle_id = $request->vehicle_id;
        if ($move_status == 3) {
            $confirm_status = 9;
        } elseif ($move_status == 5) {
            $confirm_status = 7;
        } elseif ($move_status == 6) {
            $confirm_status = 8;
        }

        $period2 = $request->period2_time;
        $period3 = $request->period3_time;
        $period2_miles = $request->period2_miles;
        $period3_miles = $request->period3_miles;
        // create array of status logs location is status number
        $log_status[1] = $request->confirm_time; //1 status for confirm trip
        $log_status[3] = $request->start_time; // 3for start trip
        $log_status[4] = $request->pickup_time; // 4 arrived at pickup
        $log_status[6] = $request->member_on_board_time; // 6 member_on_board
        $log_status[7] = $request->no_show_time; //7 no show
        $log_status[8] = $request->member_cancelled_time; //8 member_cancelled_time
        $log_status[9] = $request->drop_time;

        // $timezone_id  = $request->timezone;
        // $timezoneObj =  TimezoneMaster::where('id', $timezone_id)->first();
        $timezoneName = eso()->timezone; //$timezoneObj->name;
        // $timezoneLongName = $timezoneObj->long_name;

        DB::beginTransaction();
        try {
            $collectAutoAssign = array();
            $validator = Validator::make($request->all(), [
                'trip_id' => 'required|numeric',
                'driver_id' => 'required|numeric',
                'trip_price' => 'required|numeric',
                'total_price' => 'required|numeric',
                'adjusted_price' => 'numeric',
                'status_id' => 'required|numeric',
                'confirm_time' => 'date_format:Y-m-d H:i:s',
                'start_time' => 'date_format:Y-m-d H:i:s',
                'pickup_time' => 'date_format:Y-m-d H:i:s',
                'member_on_board_time' => 'date_format:Y-m-d H:i:s',
                'no_show_time' => 'date_format:Y-m-d H:i:s',
                'member_cancelled_time' => 'date_format:Y-m-d H:i:s',
                'drop_time' => 'date_format:Y-m-d H:i:s',
                'period2_time' => 'date_format:H:i:s',
                'period3_time' => 'date_format:H:i:s',
                'period2_miles' => 'numeric',
                'period3_miles' => 'numeric',
            ]);
            if ($validator->fails()) {
                return metaData(false, $request, '2011', '', $error_code = 400, '', $validator->messages());
            }
            $isValidDriverEso = Logics::isDriverValid($request->driver_id, $request->eso_id);
            if (!$isValidDriverEso) {
                return   metaData(false, $request, '2011', '', $error_code = 400, '', 'Invalid driver_id');
            }
            $isValidTrip = Logics::isTripValid($request->trip_id, $request->eso_id);
            if (!$isValidTrip) {
                return   metaData(false, $request, '2011', '', $error_code = 400, '', 'Invalid trip_id');
            }
            $trip_id = $request->trip_id;
            $trips = TripMaster::select('id', 'status_id', 'driver_id', 'confirm_status', 'total_price', 'adjusted_price', 'trip_price', 'date_of_service')
                ->where('id', $trip_id)
                ->first();
            if ($trips) {
                $old_status_id = $trips->status_id;
                if ($trips->driver_id == null || $trips->driver_id != $set_trip_driver) {
                    $vehicle_detail = Vehicle::where('id', $vehicle_id)->where('status', '1')->first();
                    if (!$vehicle_detail) {
                        return metaData(false, $request, 2011, '', 400, '', 'Please activate driver vehicle. ');
                    }
                    $check = $this->checkDriverIsValidAssignTrip($set_trip_driver, $trips->date_of_service);
                    if ($check['valid'] == 0) {
                        $arrNotUpdate[]['trip_id'] = $trip_id;
                        $arrNotUpdate[]['reason'] = $check['msg'];
                        return metaData(false, $request, 2011, '', 400, '', $check['msg']);
                    }
                    if ($trip_id > 0) {
                        $where = array(
                            'trip_id' => $trip_id,
                            'driver_id' => $set_trip_driver,
                        );
                        TripPayoutDetail::where($where)->delete();
                    }
                    // $trip = TripMaster::select('county_type')->where('id', $trip_id)->first();
                    $insert_payout = array(
                        'trip_id' => $trip_id,
                        'driver_id' => $set_trip_driver,
                        'user_id' => eso()->id,
                    );
                    TripPayoutDetail::create($insert_payout);
                    foreach ($log_status as $k_status_id => $l_status) {
                        if ($log_status[$k_status_id] != null && $log_status[$k_status_id] != '') {
                            $trip_datetime = date('Y-m-d H:i:s', strtotime($log_status[$k_status_id]));
                            $trip_datetime_year = date('Y', strtotime($log_status[$k_status_id]));

                            if ($trip_datetime_year != '1970' || $trip_datetime_year != '1969') {
                                $trip_datetime = getTimezone($trip_datetime, $timezoneName)->format('Y-m-d H:i:s');

                                $insert_data = array(
                                    "driver_id" => $set_trip_driver,
                                    "user_id" => $trips->user_id,
                                    "trip_id"   => $trip_id,
                                    "status"    => $k_status_id,
                                    "date_time" => $trip_datetime,
                                    'timezone' => $timezoneName,
                                    "is_updated" => 1
                                );
                                TripStatusLog::create($insert_data);
                            }
                        }
                    }

                    $trip_logs = TripLog::select('id')->where('trip_id', $trip_id)->where('driver_id', $set_trip_driver)->first();
                    if ($trip_logs) {
                        $updateArr = array(
                            "driver_id" => $set_trip_driver,
                            "period2" => $period2,
                            "period3" => $period3,
                            "period2_miles" => $period2_miles,
                            "period3_miles" => $period3_miles,
                            "is_updated" => 1
                        );
                        TripLog::where('trip_id', $trip_id)->where('driver_id', $set_trip_driver)->update($updateArr);
                    } else {
                        $insertArr = array(
                            "driver_id" => $set_trip_driver,
                            "user_id" => $trips->user_id,
                            "trip_id" => $trip_id,
                            "date_of_service" => $trips->date_of_service,
                            "period2" => $period2,
                            "period3" => $period3,
                            "period2_miles" => $period2_miles,
                            "period3_miles" => $period3_miles,
                            "is_updated" => 1
                        );
                        TripLog::create($insertArr);
                    }

                    $driver_id = $set_trip_driver;
                } elseif ($trips->driver_id > 0) {
                    foreach ($log_status as $k_status_id => $l_status) {
                        if ($log_status[$k_status_id] != null && $log_status[$k_status_id] != '') {
                            $trip_datetime = date('Y-m-d H:i:s', strtotime($log_status[$k_status_id]));
                            $trip_datetime_year = date('Y', strtotime($log_status[$k_status_id]));

                            if ($trip_datetime_year != '1970' || $trip_datetime_year != '1969') {
                                // $user_timezone =  auth()->user()->timezone;
                                $trip_datetime = getTimezone($trip_datetime, $timezoneName)->format('Y-m-d H:i:s');

                                $trip_log_status = TripStatusLog::select('id')->where('trip_id', $trip_id)->where('status', $k_status_id)->where('driver_id', $trips->driver_id)->first();

                                if ($trip_log_status) {
                                    $update_data = array(
                                        "status"    => $k_status_id,
                                        "date_time" => $trip_datetime,
                                        'timezone' => $timezoneName,
                                    );
                                    TripStatusLog::where('id', $trip_log_status->id)->update($update_data);
                                } else {
                                    $insert_data = array(
                                        "driver_id" => $trips->driver_id,
                                        "user_id" => $trips->user_id,
                                        "trip_id"   => $trip_id,
                                        "status"    => $k_status_id,
                                        "date_time" => $trip_datetime,
                                        'timezone' => $timezoneName,
                                    );
                                    TripStatusLog::create($insert_data);
                                }
                            }
                        }
                    }

                    $trip_logs = TripLog::select('id')->where('trip_id', $trip_id)->where('driver_id', $trips->driver_id)->first();
                    if ($trip_logs) {
                        $updateArr = array(
                            "driver_id" => $trips->driver_id,
                            "user_id" => $trips->user_id,
                            "trip_id" => $trip_id,
                            "date_of_service" => $trips->date_of_service,
                            "period2" => $period2,
                            "period3" => $period3,
                            "period2_miles" => $period2_miles,
                            "period3_miles" => $period3_miles
                        );
                        TripLog::where('trip_id', $trip_id)->where('driver_id', $trips->driver_id)->update($updateArr);
                    } else {
                        $insertArr = array(
                            "driver_id" => $trips->driver_id,
                            "user_id" => $trips->user_id,
                            "trip_id" => $trip_id,
                            "date_of_service" => $trips->date_of_service,
                            "period2" => $period2,
                            "period3" => $period3,
                            "period2_miles" => $period2_miles,
                            "period3_miles" => $period3_miles
                        );
                        TripLog::create($insertArr);
                    }

                    $driver_id = $trips->driver_id;
                }

                $where = array(
                    'id' => $trip_id
                );
                $updatetrip = array(
                    'status_id' => $move_status,
                    'confirm_status' => $confirm_status,
                    'vehicle_id' => $vehicle_id,
                    'Driver_id' => $driver_id,
                );

                $updatetrip['trip_price'] = $trip_price;
                $updatetrip['adjusted_price'] = $adjusted_price;
                $updatetrip['total_price'] = $total_price;
                TripMaster::where($where)->update($updatetrip);

                $msg = 'Trip Logs updated.';
                $inv_exist = RelInvoiceItem::where('trip_id', $trip_id)->count();

                if ($inv_exist > 0 && ($trips->total_price != $total_price || $trips->adjusted_price != $adjusted_price || $trips->trip_price != $trip_price)) {
                    //updatetripsprofit already exist on tripPriceUpdateAllOver
                    $this->tripPriceUpdateAllOver($trip_id);
                } else {
                    $this->updateTripsProfit($trip_id);
                }

                $this->checkLogsGenerate($trip_id, $driver_id);
                $this->driverLogPercent($trip_id, $driver_id);

                DB::commit();
                return  $this->edit($request);
                // return response()->json(["status" => 1, 'msg' => $msg]);
            } else {
                return metaData(false, $request, 2011, '', 400, '', 'Trip id not found. ');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return metaData(false, $request, 2011, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
