<?php

namespace App\Traits;

use App\Logics\Logics;
use App\Models\BaseLocation;
use App\Models\DriverLeaveDetail;
use App\Models\DriverMaster;
use App\Models\DriverNotification;
use App\Models\DriverUtilizationDetail;
use App\Models\TripMaster;
use App\Models\TripPayoutDetail;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait DriverTrait
{
    public function checkDriverIsValidAssignTrip($driver_id, $date_of_service)
    {
        $driver = DriverMaster::select('license_expiry', 'vehicle_id')->where('id', $driver_id)->first();
        $license_expiry = $driver->license_expiry;
        $vehicle_id = $driver->vehicle_id;
        $msg = '';
        $valid = 1;
        if ($date_of_service != '' && $license_expiry != '') {
            $days = timestampDifference($date_of_service, $license_expiry);
            if ($days < 0) {
                $valid = 0;
                $msg = "Driver's license will be expired for this trip.";
            }
        }

        if ($vehicle_id != null && $vehicle_id != '' && $vehicle_id != '0' && $valid == 1) {
            $vehicle = Vehicle::select('registration_expiry_date', 'insurance_expiry_date')->where('id', $vehicle_id)->first();

            if (isset($vehicle) && $vehicle->registration_expiry_date != '') {
                $registration_expiry_date = $vehicle->registration_expiry_date;
                $insurance_expiry_date = $vehicle->insurance_expiry_date;

                if ($date_of_service != '' && $registration_expiry_date != '') {
                    $days = timestampDifference($date_of_service, $registration_expiry_date);

                    if ($days < 0) {
                        $valid = 0;
                        $msg = 'The Driver vehicle registration is expiring before outgoing trip.';
                    }
                }

                if ($date_of_service != '' && $insurance_expiry_date != '' && $valid == 1) {
                    $days = timestampDifference($date_of_service, $insurance_expiry_date);

                    if ($days < 0) {
                        $valid = 0;
                        $msg = 'The Driver vehicle insurance is expiring before outgoing trip.';
                    }
                }
            }
        }
        //driver leave
        $date_of_service = date('Y-m-d', strtotime($date_of_service));
        $leave_data = DriverLeaveDetail::whereRaw("'$date_of_service' BETWEEN start_date AND end_date")->where('status', '1')->where('driver_id', $driver_id)->first();

        if ($leave_data) {
            $valid = 0;
            $msg = 'The driver is on leave.';
        }

        if ($msg != '') {
            $msg .= ' Please Assign another driver.';
        }

        return array('valid' => $valid, 'msg' => $msg);
    }
    public function assignLogic($driver_id, $trip_id, $assign_flag = 1, $notification_flag = 1) //notification 1 =send push notification eslse 0 not send notification
    {
        // return $trip_id; //$driver_id;
        $driver_pay = $this->unloadedDriverPay($driver_id, $trip_id);
        $total_driver_pay = $driver_pay['total_pay_to_driver'] ?? 0;
        $driver_detail = DriverMaster::where('id', $driver_id)->first();
        $vehicle_id = $driver_detail->vehicle_id ?? '';
        if ($assign_flag == 1) {
            $updateData = array(
                'driver_id' => $driver_id,
                'vehicle_id' => $vehicle_id,
                'status_id' => 1,
                'driver_earning' => $total_driver_pay
            );
        } else {
            $updateData = array(
                'driver_id' => $driver_id,
                'vehicle_id' => $vehicle_id,
                'status_id' => 7,
                'confirm_status' => '0',
                'driver_earning' => $total_driver_pay
            );
        }
        $isupdate = TripMaster::where('id', $trip_id)->update($updateData);
        // $arrUpdate[] = $trip_id;
        //trip and driver old payout remove
        if ($trip_id > 0) {
            $where = array(
                'trip_id' => $trip_id,
                'driver_id' => $driver_id,
            );
            TripPayoutDetail::where($where)->delete();
        }

        // $trip = TripMaster::select('county_type')->where('id', $trip_id)->first();
        $insert_payout = array(
            'trip_id' => $trip_id,
            'driver_id' => $driver_id,
            'user_id' => eso()->id,
        );
        TripPayoutDetail::create($insert_payout);

        $trip = TripMaster::where('id', $trip_id)->first();
        if ($trip->trip_start_address != '') {
            $updateData = array(
                'trip_start_address' => null,
                'trip_id_for_base_location_address' => null
            );
            $update = TripMaster::where('id', $trip_id)->update($updateData);
            $pickup_address = $trip->pickup_address;
            $base_location_id = $trip->base_location_id;
            $location = BaseLocation::where('id', $base_location_id)->first();
            if ($location) {
                $baselocation_address = $location->address;
            } else {
                $baselocation_address = '';
            }

            if ($pickup_address != '' && $baselocation_address != '') {
                $apiKey = getGoogleKeyApi(eso()->id);
                if ($apiKey != '' && $apiKey != null) {
                    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=matrix&origins=" . urlencode($baselocation_address) . "&destinations=" . urlencode($pickup_address) . "&key=" . $apiKey;
                    $json_timezone = file_get_contents($url);
                    $data = json_decode($json_timezone, true);
                    $miles = 0;

                    if (isset($data['rows'][0]['elements'][0]['status']) && $data['rows'][0]['elements'][0]['status'] == 'OK') {
                        $distance = $data['rows'][0]['elements'][0]['distance']['text'];
                        $duration = $data['rows'][0]['elements'][0]['duration']['value'];

                        if (strpos($distance, 'km') !== false) {
                            $km = trim(str_replace('km', '', $distance));
                            $factor = 0.621371;
                            $miles = number_format(str_replace(',', '', $km) * $factor, 2, '.', '');
                        } elseif (strpos($distance, 'mi') !== false) {
                            $miles = trim(str_replace('mi', '', $distance));
                        } elseif (strpos($distance, 'm') !== false) {
                            $meter = trim(str_replace('m', '', $distance));
                            $factor = 0.00062137119;
                            $miles = number_format(str_replace(',', '', $meter) * $factor, 2, '.', '');
                        }

                        if ($miles > 0) {
                            $where = array(
                                'driver_id' => $driver_id,
                                'id' => $trip_id
                            );
                            TripMaster::where($where)->update(array('estimated_mileage_frombase_location' => $miles, 'estimated_duration_frombase_location' => $duration));
                            $driver_pay =  $this->unloadedDriverPay($driver_id, $trip_id);
                            $total_driver_pay = $driver_pay['total_pay_to_driver'] ?? 0;
                            TripMaster::where($where)->update(array('driver_earning' => $total_driver_pay));
                        }
                    }
                }
            }
        }
        //Notification
        if ($notification_flag == 1) {
            $this->sendDriverNotification($driver_detail, $trip_id, $assign_flag, $notification_flag);
        }
        return 1; //success
    }
    public function sendDriverNotification($driver_detail, $trip_id, $assign_flag = 1, $notification_flag = 1)
    {
        //Notification
        $id = $driver_detail->id;
        $device_token = $driver_detail->device_token;
        if ($assign_flag == 1) {
            $message = "You have been assigned a new trip. Click to view details.";
            $notification_type = "Assign";
            $title = "Trip Assigned";
        } else {
            $name = $driver_detail->name;
            $device_token = $driver_detail->device_token;
            $message = "You have been reassigned a trip. Click to view details.";
            $notification_type = "Reassign";
            $title = "Driver Reassigned";
        }
        if ($device_token) {
            if ($notification_flag == 1) {
                $insert_arr = array("driver_id" => $id, "post_by" => eso()->id, "notification" => $message, "is_read" => 0, "created_at" => date('Y-m-d H:i:s'), "link" => $trip_id);
                DriverNotification::insert($insert_arr);
                $badge = Drivernotification::where('driver_id', $id)->where('is_read', 0)->get()->count();
                // if ($driver_detail->device_type == 'Android') {
                Logics::sendPushNotification($device_token, $message, $title, $badge, $trip_id);
                // } else {
                //     $this->pushNotificationIos($device_token, $message, $notification_type, $title, $badge, $trip_id);
                // }
            }
        }
    }
    public function unloadedDriverPay($driver_id, $trip_id)
    {
        $trip_check_county = TripMaster::select('county_type')->where('id', $trip_id)->first();
        $table = "trip_master_ut AS tr";
        $trips = DB::table($table);
        if ($trip_check_county->county_type == 1) { //county rates
            $trips = $trips->select('tr.payout_type', 'tr.trip_format', 'tr.total_price', 'dr.name', 'dr.rate_updated_at', DB::raw('tr.estimated_mileage_frombase_location as unloaded_miles'), DB::raw('tr.estimated_trip_distance as loaded_miles'), DB::raw('tr.estimated_duration_frombase_location as unloaded_minutes'), DB::raw('tr.estimated_trip_duration as loaded_minutes'), 'tr.status_id', 'sr.unloaded_rate_per_mile', 'sr.loaded_rate_per_mile', 'sr.unloaded_rate_per_min', 'sr.loaded_rate_per_min', 'sr.unloaded_rate_per_hr', 'sr.loaded_rate_per_hr', 'sr.insurance_rate_per_mile', 'sr.base_rate', 'sr.unloaded_base_rate', 'sr.loaded_base_rate', 'sr.wait_time_per_hour', 'sr.passenger_no_show', 'sr.late_cancellation', 'sr.minimum_payout', 'sr.base_rate_bmm', 'sr.loaded_rate_per_mile_bmm', 'sr.loaded_base_rate_bmm');
        } else { //out of county rates
            $trips = $trips->select('tr.payout_type', 'tr.trip_format', 'tr.total_price', 'dr.name', 'tr.payment_status', 'dr.rate_updated_at', DB::raw('tr.estimated_mileage_frombase_location as unloaded_miles'), DB::raw('tr.estimated_trip_distance as loaded_miles'), DB::raw('tr.estimated_duration_frombase_location as unloaded_minutes'), DB::raw('tr.estimated_trip_duration as loaded_minutes'), 'tr.status_id', DB::raw('sr.unloaded_rate_per_mile_out as unloaded_rate_per_mile'), DB::raw('sr.loaded_rate_per_mile_out as loaded_rate_per_mile'), DB::raw('sr.unloaded_rate_per_min_out as unloaded_rate_per_min'), DB::raw('sr.loaded_rate_per_min_out as loaded_rate_per_min'), DB::raw('sr.unloaded_rate_per_hr_out as unloaded_rate_per_hr'), DB::raw('sr.loaded_rate_per_hr_out as loaded_rate_per_hr'), DB::raw('sr.insurance_rate_per_mile_out as insurance_rate_per_mile'), DB::raw('sr.base_rate_out as base_rate'), DB::raw('sr.unloaded_base_rate_out as unloaded_base_rate'), DB::raw('sr.loaded_base_rate_out as loaded_base_rate'), DB::raw('sr.wait_time_per_hour_out as wait_time_per_hour'), DB::raw('sr.passenger_no_show_out as passenger_no_show'), DB::raw('sr.late_cancellation_out as late_cancellation'), DB::raw('sr.minimum_payout_out as minimum_payout'), DB::raw('sr.base_rate_bmm_out as base_rate_bmm'), DB::raw('sr.loaded_rate_per_mile_bmm_out as loaded_rate_per_mile_bmm'), DB::raw('sr.loaded_base_rate_bmm_out as loaded_base_rate_bmm'));
        }
        $trips = $trips->where('tr.driver_id', $driver_id)
            ->where('tr.id', $trip_id)
            ->leftJoin('driver_service_rates AS sr', function ($join) {
                $join->on('tr.driver_id', '=', 'sr.driver_id')
                    ->on('tr.master_level_of_service_id', '=', 'sr.level_of_service_id');
            })
            ->leftJoin('driver_master_ut AS dr', function ($join) {
                $join->on('tr.driver_id', '=', 'dr.id');
            })->get();
        $res = array();
        $total_miles = 0;
        foreach ($trips as $trip123) {
            $trip = (array) $trip123;
            $total_price = $trip["total_price"];
            if ($total_price == '') {
                $total_price = 0;
            }
            $unloaded_rate_per_mile = $trip["unloaded_rate_per_mile"];
            $loaded_rate_per_mile = $trip["loaded_rate_per_mile"];
            $unloaded_rate_per_min = $trip['unloaded_rate_per_min'];
            $loaded_rate_per_min = $trip['loaded_rate_per_min'];
            $base_rate = $trip['base_rate'];
            $unloaded_base_rate = $trip['unloaded_base_rate'];
            $loaded_base_rate = $trip['loaded_base_rate'];
            // new code start here for change driver fees 0 if type new
            // Personal Commercial
            $driver_details = DriverMaster::where('id', $driver_id)->first();
            if ($driver_details->insurance_type == 'Personal Commercial') {
                $insurance_rate_per_mile = 0;
            } else {
                $insurance_rate_per_mile = $trip["insurance_rate_per_mile"];
            }
            $wait_time_per_hour = $trip["wait_time_per_hour"];
            $minimum_payout = $trip['minimum_payout'];
            $deduction_amt =  0;
            $reimbursement_amt =  0;
            $base_rate_bmm = $trip['base_rate_bmm'];
            $loaded_rate_per_mile_bmm = $trip['loaded_rate_per_mile_bmm'];
            $loaded_base_rate_bmm = $trip['loaded_base_rate_bmm'];

            if ($trip["unloaded_miles"] == '') {
                $unloaded_miles = 0;
            } else {
                $unloaded_miles = $trip["unloaded_miles"];
            }

            if ($trip["loaded_miles"] == '') {
                $loaded_miles = 0;
            } else {
                $loaded_miles = $trip["loaded_miles"];
            }

            if ($trip["unloaded_minutes"] == '') {
                $unloaded_minutes = 0;
            } else {
                $unloaded_minutes = $trip["unloaded_minutes"];
            }

            if ($trip["loaded_minutes"] == '') {
                $loaded_minutes = 0;
            } else {
                $loaded_minutes = $trip["loaded_minutes"];
            }

            if ($trip["trip_format"] == 4) {
                if ($trip["wait_time_sec"] != '' && $trip["wait_time_sec"] != '0') {
                    $wait_time = secondToTimes($trip["wait_time_sec"]);
                } else {
                    $wait_time = '0';
                }
            } else {
                $wait_time = 'NA';
            }
            /////////
            $res['unloaded_miles'] = $unloaded_miles;
            $res['unloaded_minutes'] = $unloaded_minutes;
            $res['loaded_miles'] = $loaded_miles;
            $res['loaded_minutes'] = $loaded_minutes;
            $res['wait_time'] = $wait_time;
            /////////
            if ($trip['payout_type'] == 1) {
                $payout = "Mileage";
                $total_miles = $unloaded_miles + $loaded_miles;
                $unloaded_pay = $unloaded_miles * $unloaded_rate_per_mile;
                $loaded_pay = $loaded_miles * $loaded_rate_per_mile;
                $total_pay = round($unloaded_pay + $loaded_pay, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;

                if ($trip["trip_format"] == 4 && $trip['wait_time'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round($total_pay + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }

                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);
                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }
                $total_pay_to_driver = $total_pay + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 2) {
                $payout = "Flat Rate";
                $total_miles = $unloaded_miles + $loaded_miles;
                $flat_rate = round(($trip["flat_rate"] / 100) * $total_price, 2);

                $driver_fee = $total_miles * $insurance_rate_per_mile;
                $total_pay = $flat_rate;
                if ($trip["trip_format"] == 4 && $trip['wait_time'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round(($total_pay) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }

                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);

                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }
                $total_pay_to_driver = $total_pay + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 3) {
                $payout = "Time";
                $total_miles = $unloaded_miles + $loaded_miles;

                $unloaded_sec = $unloaded_minutes;
                $unload_minutes = round(($unloaded_sec / 60), 5);
                $unloaded_pay = $unload_minutes * $unloaded_rate_per_min;
                $loaded_sec = $loaded_minutes;
                $load_minutes = round(($loaded_sec / 60), 5);
                $loaded_pay = $load_minutes * $loaded_rate_per_min;
                $total_pay_minutes = round($unloaded_pay + $loaded_pay, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                $total_pay = $total_pay_minutes;
                if ($trip["trip_format"] == 4 && $trip['wait_time'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round(($total_pay) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }
                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);

                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }
                $total_pay_to_driver = $total_pay + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 4) {
                $payout = "Base rate with time";
                $total_miles = $unloaded_miles + $loaded_miles;
                $base_rate = round(($base_rate / 100) * $total_price, 2);
                $unloaded_sec = $unloaded_minutes;
                $unload_minutes = round(($unloaded_sec / 60), 5);
                $unloaded_pay = round($unload_minutes * $unloaded_base_rate, 2);
                $loaded_sec = $loaded_minutes;
                $load_minutes = round(($loaded_sec / 60), 5);
                $loaded_pay = round($load_minutes * $loaded_base_rate, 2);
                $total_pay = round($base_rate + $unloaded_pay + $loaded_pay, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                if ($trip["trip_format"] == 4 && $trip['wait_time'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round(($total_pay) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }
                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);

                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }
                $total_pay_to_driver = $total_pay  + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 5 || $trip['payout_type'] == 6) {
                if ($trip['payout_type'] == 5) {
                    $payout = "No show";
                } else {
                    $payout = "Late Cancellation";
                }
                $total_miles = $unloaded_miles; // $loaded_miles +
                $flat_rate = round(($trip["flat_rate"] / 100) * $total_price, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                $total_pay = $flat_rate;

                $wait_time_sec = '';
                $wait_time_amount = 0;

                $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);
                if ($total_pay < $minimum_payout_val) {
                    if ($reimbursement_amt != 0.00) {
                        $minimum_payout_val = round(($minimum_payout_val) + $reimbursement_amt, 2);
                    }

                    if ($deduction_amt != 0.00) {
                        $minimum_payout_val = round(($minimum_payout_val) - $deduction_amt, 2);
                    }
                    $total_pay = $minimum_payout_val;
                }
                $total_pay_to_driver = $total_pay  + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 7) {
                $payout = "Mileage+Time";
                $total_miles = $unloaded_miles + $loaded_miles;
                $unloaded_pay_A = round($unloaded_miles * $unloaded_rate_per_mile, 2);
                $loaded_pay_A = round($loaded_miles * $loaded_rate_per_mile, 2);
                $total_pay_A = round($unloaded_pay_A + $loaded_pay_A, 2);
                $unloaded_sec = $unloaded_minutes;
                $unload_minutes = round(($unloaded_sec / 60), 5);
                $unloaded_pay_C = round($unload_minutes * $unloaded_rate_per_min, 2);
                $loaded_sec = $loaded_minutes;
                $load_minutes = round(($loaded_sec / 60), 5);
                $loaded_pay_C = round($load_minutes * $loaded_rate_per_min, 2);
                $total_pay_C = $unloaded_pay_C + $loaded_pay_C;
                $total_pay = $total_pay_A + $total_pay_C;
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                $total_pay_to_driverA = max(($total_pay_A), 0);
                $total_pay_to_driverC = max(($total_pay_C), 0);
                $total_pay_to_driver =  round(($total_pay_to_driverA) +  ($total_pay_to_driverC), 2);
                if ($trip["trip_format"] == 4 && $trip['wait_time'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay_to_driver = round(($total_pay_to_driver) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }
                if ($deduction_amt != 0.00) {
                    $total_pay_to_driver = ($total_pay_to_driver) - $deduction_amt;
                }
                if ($reimbursement_amt != 0.00) {
                    $total_pay_to_driver = ($total_pay_to_driver) + $reimbursement_amt;
                }
                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);
                    if ($total_pay_to_driver < $minimum_payout_val) {
                        if ($reimbursement_amt != 0.00) {
                            $minimum_payout_val = ($minimum_payout_val) + $reimbursement_amt;
                        }
                        if ($deduction_amt != 0.00) {
                            $minimum_payout_val = ($minimum_payout_val) - $deduction_amt;
                        }
                        $total_pay_to_driver = $minimum_payout_val;
                    }
                }

                $company_profitA = round($total_price - $total_pay_A, 2);
                $company_profitC = round($total_price - $total_pay_C, 2);
                $company_profit = round($company_profitA + $company_profitC, 2);
                $company_profit = round(($company_profit + $reimbursement_amt) - $deduction_amt, 2);
            } elseif ($trip['payout_type'] == 8) {
                $payout = "Base Rate Per Mile Per Min";
                $total_miles = $unloaded_miles + $loaded_miles;
                $flatrate = round(($base_rate_bmm / 100) * $total_price, 2);
                $total_loaded_cost_miles = round($loaded_miles * $loaded_rate_per_mile_bmm, 2);
                $loaded_sec = $loaded_minutes;
                $load_minutes = round(($loaded_sec / 60), 5);
                $total_loaded_cost_min = round($loaded_base_rate_bmm * $load_minutes, 2);
                $total_pay = round($flatrate + $total_loaded_cost_miles + $total_loaded_cost_min, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                if ($trip["trip_format"] == 4 && $trip['wait_time'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round(($total_pay) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }

                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);
                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }
                $total_pay_to_driver = $total_pay  + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } else {
                $driver_fee = 0;
                $total_pay_to_driver = 0;
                $company_profit = 0;
                $payout = "";
                $wait_time_amount = 0;
            }
            $res['total_pay_to_driver'] = number_format(decimal2digitNumber($total_pay_to_driver), 2, '.', '');
        }

        return $res;
    }
    public function getDriverOverTime($start_date, $end_time, $driver_id, $given_payout_detail)
    {
        $id = $driver_id;
        $current_week_start = Carbon::parse($start_date, eso()->timezone)
            ->startOfDay()
            ->setTimezone(config('app.timezone'));
        $current_week_end = Carbon::parse($end_time, eso()->timezone)
            ->endOfDay()
            ->setTimezone(config('app.timezone'));
        ////////////define variable for this payout
        $hourly_rate = 0;
        $down_time_rate = 0;
        $over_time_rate = 0; //parcentage

        $trips = TripMaster::select('id', 'driver_id', 'total_price', 'trip_format')
            ->with('driver')
            ->with('log')
            ->whereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN trip_master_ut.shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) >="' . $current_week_start . '"')
            ->WhereRaw('concat(trip_master_ut.date_of_service," ", CASE WHEN trip_master_ut.shedule_pickup_time IS NULL THEN "00:00:00" ELSE trip_master_ut.shedule_pickup_time END) <="' . $current_week_end . '"')
            ->where('driver_id', $id)
            ->where(function ($query) {
                $query->where('payment_status', 0)
                    ->orWhereNull('payment_status');
            })
            ->whereIn('status_id', ['3', '5', '6'])
            ->groupBy('date_of_service')
            ->eso()
            ->get();
        $total_trip_prices = 0;
        $total_loaded_durations_sec = 0;
        $total_unloaded_durations_sec = 0;
        $total_trip_durations_sec = 0;
        $total_wait_time_sec = 0;
        $array_wait_time = array();
        $array_unloaded_time = array();
        $array_loaded_time = array();
        if ($trips) {
            $total_price = $trips->total_price;
            $total_trip_prices += $trips->total_price;
            if ($total_price == '') {
                $total_price = 0;
            }
            if ($given_payout_detail != null) {
                $over_time_sec = $given_payout_detail['over_time_sec'];
                $hourly_rate = $given_payout_detail['hourly_rate'];
                $down_time_rate = $given_payout_detail['down_time_rate'];
            }
            $over_time_rate = $given_payout_detail['over_time_rate_per_hour']; //$trip['over_time_rate'];

            $unloaded_minutes = $trips->log->period2 ?? 0;
            $loaded_minutes = $trips->log->period3 ?? 0;
            $current_wait_time = 0;
            if ($trips->trip_format == 4) {
                $wait_time_sec = $trips->log->wait_time_sec ??  '';
                if ($wait_time_sec != '' && $wait_time_sec != '0') {
                    if ($wait_time_sec > 0) {
                        array_push($array_wait_time, secondToTimes($wait_time_sec));
                        $total_wait_time_sec += $wait_time_sec;
                        $current_wait_time = $wait_time_sec;
                    }
                }
            }
            $loaded_secs = strtotime($loaded_minutes) - strtotime("00:00:00");
            if ($loaded_secs < 0) {
                $loaded_secs = 0;
            }
            $total_loaded_durations_sec += $loaded_secs;
            $unloaded_secs = strtotime($unloaded_minutes) - strtotime("00:00:00");
            if ($unloaded_secs < 0) {
                $unloaded_secs = 0;
            }

            array_push($array_unloaded_time, secondToTimes($unloaded_secs));
            array_push($array_loaded_time, secondToTimes($loaded_secs));
            $total_unloaded_durations_sec += $unloaded_secs;
            $total_seconds = $loaded_secs + $unloaded_secs + $current_wait_time;
            $total_trip_durations_sec += $total_seconds;
            $over_time_sec = $over_time_sec; //$over_time_hours_value * 3600;
            $normal_working_sec = $total_trip_durations_sec - $over_time_sec;
            $normal_time_payout = ($hourly_rate / 3600) * $normal_working_sec;
            $over_time_payout = ($over_time_rate / 3600) * $over_time_sec;

            $total_payout = decimal2digitNumber($normal_time_payout) + decimal2digitNumber($over_time_payout);
            $total_count = $total_trip_durations_sec; //$normal_working_sec + $over_time_sec; //$total_wait_time_sec + $total_unloaded_durations_sec + $total_loaded_durations_sec;

            ///////////////////////////////down time payout
            $total_clockin_time = $this->driverClockInTimeBetweenDate($start_date, $end_time, $driver_id);
            $time_explode = explode(':', $total_clockin_time);
            $total_clockin_time_seconds = $time_explode[0] * 3600 + $time_explode[1] * 60 + $time_explode[2];
            $working_time_seconds = $total_count;
            $total_downtime_seconds = 0;
            $total_downtime = '00:00:00';
            $down_time_payout = 0;
            if ($total_clockin_time_seconds > 0) {
                $total_downtime_seconds = $total_clockin_time_seconds - $working_time_seconds;
                $total_downtime = secondToTimes($total_downtime_seconds);
                $down_time_payout = ($down_time_rate / 3600) * $total_downtime_seconds;
            }

            $return_data['total_downtime'] = $total_downtime;
            $return_data['down_time_seconds'] = $total_downtime_seconds;
            $return_data['total_clockin_time'] = $total_clockin_time;
            $return_data['down_time_payout'] = decimal2digitNumber($down_time_payout);
            ///////////////////////////////
            $return_data['all_trip_price'] = decimal2digitNumber($total_trip_prices);
            $return_data['total_wait_time'] = secondToTimes($total_wait_time_sec);
            $return_data['total_unloaded_time'] = secondToTimes($total_unloaded_durations_sec);
            $return_data['total_loaded_time'] = secondToTimes($total_loaded_durations_sec);
            $return_data['total_time'] = secondToTimes($total_count);
            $return_data['total_seconds'] = $total_count;
            $return_data['normal_working_time'] = secondToTimes($normal_working_sec);
            $return_data['normal_working_seconds'] = $normal_working_sec;
            // return $return_data['normal_working_time'];
            $return_data['over_working_time'] = secondToTimes($over_time_sec);
            $return_data['total_over_time_payout'] = decimal2digitNumber($over_time_payout);
            $return_data['total_normal_time_payout'] = decimal2digitNumber($normal_time_payout);
            $return_data['total_payout'] = decimal2digitNumber($total_payout);
            $return_data['trips_count'] = count($trips);
            // extra
            $return_data['array_wait_time'] = $array_wait_time;
            $return_data['array_unloaded_time'] = $array_unloaded_time;
            $return_data['array_loaded_time'] = $array_loaded_time;
            $return_data['final_payout'] = decimal2digitNumber($down_time_payout + $total_payout);
            return $return_data;
        } else {
            return array();
        }
    }
    public function driverClockInTimeBetweenDate($start_date, $end_date, $driver_id)
    {
        $trips = DriverUtilizationDetail::select(DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(IFNULL(out_time,0)) - TIME_TO_SEC(IFNULL(in_time,0))))  as total_time'))
            ->where('driver_id', $driver_id)
            ->WhereRaw('out_time IS NOT NULL');
        if (isset($start_date) && $start_date != null) {
            $start_date = Carbon::parse($start_date, auth()->user()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));
            $trips = $trips->whereRaw('concat(utilization_date," ", CASE WHEN in_time IS NULL THEN "00:00:00" ELSE in_time END) >="' . $start_date . '"');
        }
        if (isset($end_date) && $end_date != null) {
            $end_date = Carbon::parse($end_date, auth()->user()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            $trips = $trips->WhereRaw('concat(utilization_date," ", CASE WHEN out_time IS NULL THEN "00:00:00" ELSE out_time END) <="' . $end_date . '"');
            $trips =  $trips->first();
        }
        // return $trips;
        if ($trips->total_time) {
            $get_all_duration =  explode('.', $trips->total_time);
            return $get_all_duration[0];
        } else {
            return '00:00:00';
        }
    }
    public function selectjointablesGetAll($table, $select_query, $where, $joinData, $groupBy = null, $orderBy = null, $glob_page = null, $where_in_cond = array(), $where_null_array = array(), $start_date = null, $end_date = null, $like_array = array(), $groupcond = array())
    {
        // return $start_date;
        // return $groupBy;
        $returndata = DB::table($table);
        if (count($joinData) > 0) {
            foreach ($joinData as $key => $val) {
                $table = $key;
                $joinType = $val['joinType'];

                $returndata =  $returndata->$joinType($table, function ($join) use ($val) {
                    $joinTo = $val['joinTo'];
                    $joinFrom = $val['joinFrom'];
                    $join->on($joinTo, '=', $joinFrom);

                    if (isset($val['joinTo2']) && isset($val['joinTo2'])) {
                        $joinTo2 = $val['joinTo2'];
                        $joinFrom2 = $val['joinFrom2'];
                        $join->on($joinTo2, '=', $joinFrom2);
                    }
                });
            }
        }
        $returndata = $returndata->select(DB::raw($select_query));
        $returndata = $returndata->where($where);

        foreach ($groupcond as $key => $val) {
            $returndata =  $returndata->whereRaw($val);
        }

        foreach ($where_in_cond as $key => $val) {
            $returndata =  $returndata->whereIn($key, $val);
        }
        foreach ($where_null_array as  $val) {
            $returndata =  $returndata->whereNull($val);
        }


        if ($start_date != null && $end_date != null && $start_date != 'null' && $end_date != 'null') {
            $start_date = Carbon::parse($start_date, auth()->user()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));

            $end_date = Carbon::parse($end_date, auth()->user()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));

            $returndata =  $returndata
                ->whereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) >="' . $start_date . '"')
                ->WhereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) <="' . $end_date . '"');
        } elseif ($start_date != null && $start_date != 'null') {
            $start_date = $start_date;
            $start_date = Carbon::parse($start_date, auth()->user()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));

            $returndata =  $returndata->whereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) >="' . $start_date . '"');
        } elseif ($end_date != null && $end_date != 'null') {
            $end_date = $end_date;
            $end_date = Carbon::parse($end_date, auth()->user()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            // dd($end_date->toDateTimeString());
            $returndata =  $returndata->whereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) <="' . $end_date . '"');
        }
        ////like array
        if (count($like_array) > 0) {
            $returndata = $returndata->where(function ($query) use ($like_array) {
                $i = 0;
                foreach ($like_array as $k => $v) {
                    if ($i == 0) {
                        $query->where($k, 'LIKE', '%' . $v . '%');
                    }
                    $query->orWhere($k, 'LIKE', '%' . $v . '%');
                    $i++;
                }
            });
        }
        if ($groupBy != null) {
            if (count($groupBy) > 0) {
                foreach ($groupBy as $key => $val) {
                    // return $val;
                    $returndata =  $returndata->groupBy($val);
                }
            }
        }
        if ($orderBy != null) {
            if (count($orderBy) > 0) {
                foreach ($orderBy as $key => $val) {
                    $returndata =  $returndata->orderBy($key, $val);
                }
            }
        }

        $returndata =  $returndata->get()->toArray();
        return $returndata;
    }
    public function calculateDriverPay($driver_id, $trip_id, $given_payout_detail = array(), $paid_flag = 0)
    { // paid flag == 1 then rates given in trip_payout_detail other wise given from driver table
        // return $trip_id;
        if ($paid_flag == 1) {
            $select_query = "tr.*,dr.name,pt.id as payout_id,tr.payment_status,pt.unloaded_rate_per_mile,pt.loaded_rate_per_mile,pt.unloaded_rate_per_min,pt.loaded_rate_per_min,pt.unloaded_rate_per_hr,pt.loaded_rate_per_hr,pt.insurance_rate_per_mile,pt.base_rate,pt.unloaded_base_rate,pt.loaded_base_rate,pt.wait_time_per_hour,pt.passenger_no_show,pt.late_cancellation,pt.minimum_payout,pt.deduction_amt,pt.deduction_detail,pt.reimbursement_amt,pt.reimbursement_detail,dr.rate_updated_at,stl.period2_miles as unloaded_miles,stl.period3_miles as loaded_miles,((stl.period2)) as unloaded_minutes,((stl.period3)) as loaded_minutes,((stl.period2)) as unloaded_hours,((stl.period3)) as loaded_hours,st.status_description, stl.wait_time_sec, tr.status_id, pt.base_rate_bmm, pt.loaded_rate_per_mile_bmm, pt.loaded_base_rate_bmm";
        } else {
            $trip_check_county = TripMaster::select('county_type')->where('id', $trip_id)->first();
            if ($trip_check_county->county_type == 1) { //county rates
                $select_query = "tr.*,dr.name,pt.id as payout_id,tr.payment_status,pt.deduction_amt,pt.deduction_detail,pt.reimbursement_amt,pt.reimbursement_detail,dr.rate_updated_at,stl.period2_miles as unloaded_miles,stl.period3_miles as loaded_miles,((stl.period2)) as unloaded_minutes,((stl.period3)) as loaded_minutes,((stl.period2)) as unloaded_hours,((stl.period3)) as loaded_hours,st.status_description, stl.wait_time_sec, tr.status_id,sr.unloaded_rate_per_mile,sr.loaded_rate_per_mile,sr.unloaded_rate_per_min,sr.loaded_rate_per_min,sr.unloaded_rate_per_hr,sr.loaded_rate_per_hr,sr.insurance_rate_per_mile,sr.base_rate,sr.unloaded_base_rate,sr.loaded_base_rate,sr.wait_time_per_hour,sr.passenger_no_show,sr.late_cancellation,sr.minimum_payout, sr.base_rate_bmm, sr.loaded_rate_per_mile_bmm, sr.loaded_base_rate_bmm";
            } else { //out of county rates
                $select_query = "tr.*,dr.name,pt.id as payout_id,tr.payment_status,pt.deduction_amt,pt.deduction_detail,pt.reimbursement_amt,pt.reimbursement_detail,dr.rate_updated_at,stl.period2_miles as unloaded_miles,stl.period3_miles as loaded_miles,((stl.period2)) as unloaded_minutes,((stl.period3)) as loaded_minutes,((stl.period2)) as unloaded_hours,((stl.period3)) as loaded_hours,st.status_description, stl.wait_time_sec, tr.status_id,sr.unloaded_rate_per_mile_out as unloaded_rate_per_mile,sr.loaded_rate_per_mile_out as loaded_rate_per_mile,sr.unloaded_rate_per_min_out as unloaded_rate_per_min,sr.loaded_rate_per_min_out as loaded_rate_per_min,sr.unloaded_rate_per_hr_out as unloaded_rate_per_hr,sr.loaded_rate_per_hr_out as loaded_rate_per_hr,sr.insurance_rate_per_mile_out as insurance_rate_per_mile,sr.base_rate_out as base_rate,sr.unloaded_base_rate_out as unloaded_base_rate,sr.loaded_base_rate_out as loaded_base_rate,sr.wait_time_per_hour_out as wait_time_per_hour,sr.passenger_no_show_out as passenger_no_show,sr.late_cancellation_out as late_cancellation,sr.minimum_payout_out as minimum_payout, sr.base_rate_bmm_out as base_rate_bmm, sr.loaded_rate_per_mile_bmm_out as loaded_rate_per_mile_bmm, sr.loaded_base_rate_bmm_out as loaded_base_rate_bmm";
            }
        }
        $table = "trip_master_ut AS tr";
        $joinData = array(
            "trip_payout_detail AS pt" => array('joinType' => "leftJoin", 'joinTo' => "tr.id", 'joinFrom' => "pt.trip_id", 'joinTo2' => "tr.driver_id", 'joinFrom2' => "pt.driver_id"),
            "driver_service_rates AS sr" => array('joinType' => 'leftJoin', 'joinTo' => "tr.driver_id", 'joinFrom' => 'sr.driver_id', 'joinTo2' => 'tr.master_level_of_service_id', 'joinFrom2' => 'sr.level_of_service_id'),
            "driver_master_ut AS dr" => array('joinType' => "leftJoin", 'joinTo' => "tr.driver_id", 'joinFrom' => "dr.id"),
            "trip_logs AS stl" => array('joinType' => "leftJoin", 'joinTo' => "tr.id", 'joinFrom' => "stl.trip_id", 'joinTo2' => "tr.driver_id", 'joinFrom2' => "stl.driver_id"),
            "status_master_ut AS st" => array('joinType' => "leftJoin", 'joinTo' => "tr.status_id", 'joinFrom' => "st.id"),
        );
        $cond['tr.driver_id'] = $driver_id;
        $cond['tr.id'] = $trip_id;
        $where = $cond;
        $where_in_cond = array(); // ['tr.status_id'] = $status_arr;
        $orderBy['tr.date_of_service'] = 'desc';
        $groupBy = array();
        $where_null_array = array();
        $trips = $this->selectjointablesGetAll($table, $select_query, $where, $joinData, $groupBy, $orderBy, 0, $where_in_cond, $where_null_array);
        $res = array();
        $res['driver_fee'] = 0;
        $res['insurance_amount'] = 0;
        $res['total_miles'] = 0;
        $res['total_price'] = 0;
        $res['total_pay_to_driver'] = 0;
        $res['company_profit'] = 0;
        $res['payout'] = 0;
        $res['wait_time_amount'] = 0;
        $res['driver_fee'] = decimal2digitNumber(0);
        $res['total_pay_to_driver'] = decimal2digitNumber(0);
        $res['company_profit'] = decimal2digitNumber(0);
        $res['payout'] = '';
        $res['wait_time_amount'] = decimal2digitNumber(0);
        // return DB::getQueryLog();
        // return $trips;
        //check commisson rate
        $total_miles = 0;
        foreach ($trips as $trip123) {
            $trip = (array) $trip123;
            if ($given_payout_detail != null) {
                $trip['payout_type'] = $given_payout_detail['payout_type'];
                $trip['flat_rate'] = $given_payout_detail['flat_rate'];
            }
            $total_price = $trip["total_price"];
            if ($total_price == '') {
                $total_price = 0;
            }
            $unloaded_rate_per_mile = $trip["unloaded_rate_per_mile"];
            $loaded_rate_per_mile = $trip["loaded_rate_per_mile"];
            $unloaded_rate_per_min = $trip['unloaded_rate_per_min'];
            $loaded_rate_per_min = $trip['loaded_rate_per_min'];
            $unloaded_rate_per_hr = $trip['unloaded_rate_per_hr'];
            $loaded_rate_per_hr = $trip['loaded_rate_per_hr'];
            $base_rate = $trip['base_rate'];
            $unloaded_base_rate = $trip['unloaded_base_rate'];
            $loaded_base_rate = $trip['loaded_base_rate'];
            // new code start here for change driver fees 0 if type new
            // Personal Commercial
            $driver_details = DriverMaster::where('id', $driver_id)->first();
            if ($driver_details->insurance_type == 'Personal Commercial') {
                $insurance_rate_per_mile = 0; //$trip["insurance_rate_per_mile"];
            } else {
                $insurance_rate_per_mile = $trip["insurance_rate_per_mile"];
            }

            $wait_time_per_hour = $trip["wait_time_per_hour"];
            $passenger_no_show = $trip['passenger_no_show'];
            $late_cancellation = $trip['late_cancellation'];
            $minimum_payout = $trip['minimum_payout'];
            $deduction_amt = $trip['deduction_amt'];
            $deduction_detail = $trip['deduction_detail'];
            $reimbursement_amt = $trip['reimbursement_amt'];
            $reimbursement_detail = $trip['reimbursement_detail'];

            $base_rate_bmm = $trip['base_rate_bmm'];
            $loaded_rate_per_mile_bmm = $trip['loaded_rate_per_mile_bmm'];
            $loaded_base_rate_bmm = $trip['loaded_base_rate_bmm'];

            if ($trip["unloaded_miles"] == '') {
                $unloaded_miles = 0;
            } else {
                $unloaded_miles = $trip["unloaded_miles"];
            }

            if ($trip["loaded_miles"] == '') {
                $loaded_miles = 0;
            } else {
                $loaded_miles = $trip["loaded_miles"];
            }

            if ($trip["unloaded_minutes"] == '') {
                $unloaded_minutes = 0;
            } else {
                $unloaded_minutes = $trip["unloaded_minutes"];
            }

            if ($trip["loaded_minutes"] == '') {
                $loaded_minutes = 0;
            } else {
                $loaded_minutes = $trip["loaded_minutes"];
            }

            if ($trip["unloaded_hours"] == '') {
                $unloaded_hours = 0;
            } else {
                $unloaded_hours = $trip["unloaded_hours"];
            }

            if ($trip["loaded_hours"] == '') {
                $loaded_hours = 0;
            } else {
                $loaded_hours = $trip["loaded_hours"];
            }

            if ($trip["trip_format"] == 4) {
                if ($trip["wait_time_sec"] != '' && $trip["wait_time_sec"] != '0') {
                    $wait_time = secondToTimes($trip["wait_time_sec"]);
                } else {
                    $wait_time = '0';
                }
            } else {
                $wait_time = 'NA';
            }
            /////////
            $res['unloaded_miles'] = $unloaded_miles;
            $res['unloaded_minutes'] = $unloaded_minutes;
            $res['loaded_miles'] = $loaded_miles;
            $res['loaded_minutes'] = $loaded_minutes;
            $res['wait_time'] = $wait_time;
            /////////
            if ($trip['payout_type'] == 1) {
                $payout = "Mileage";
                $total_miles = $unloaded_miles + $loaded_miles;
                $unloaded_pay = $unloaded_miles * $unloaded_rate_per_mile;
                $loaded_pay = $loaded_miles * $loaded_rate_per_mile;
                $total_pay = round($unloaded_pay + $loaded_pay, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                if ($trip["trip_format"] == 4 && $trip['wait_time_sec'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time_sec'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round($total_pay + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }
                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);
                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }
                $total_pay_to_driver = $total_pay + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 2) {
                $payout = "Flat Rate";
                $total_miles = $unloaded_miles + $loaded_miles;
                $flat_rate = round(($trip["flat_rate"] / 100) * $total_price, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                $total_pay = $flat_rate;
                if ($trip["trip_format"] == 4 && $trip['wait_time_sec'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time_sec'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round(($total_pay) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }
                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);

                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }

                $total_pay_to_driver = $total_pay + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 3) {
                $payout = "Time";
                $total_miles = $unloaded_miles + $loaded_miles;
                $unloaded_sec = minutesToSec($unloaded_minutes);
                $unload_minutes = round(($unloaded_sec / 60), 5);
                $unloaded_pay = $unload_minutes * $unloaded_rate_per_min;
                // $unloaded_rate_per_sec = $unloaded_rate_per_min/60;
                // $unloaded_pay = round($minutes * $unloaded_rate_per_sec,2);
                $loaded_sec = minutesToSec($loaded_minutes);
                $load_minutes = round(($loaded_sec / 60), 5);
                $loaded_pay = $load_minutes * $loaded_rate_per_min;
                $total_pay_minutes = round($unloaded_pay + $loaded_pay, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                $total_pay = $total_pay_minutes;

                if ($trip["trip_format"] == 4 && $trip['wait_time_sec'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time_sec'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round(($total_pay) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }

                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);
                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }
                $total_pay_to_driver = $total_pay + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 4) {
                $payout = "Base rate with time";
                $total_miles = $unloaded_miles + $loaded_miles;
                $base_rate = round(($base_rate / 100) * $total_price, 2);
                $unloaded_sec = minutesToSec($unloaded_minutes);
                $unload_minutes = round(($unloaded_sec / 60), 5);
                $unloaded_pay = round($unload_minutes * $unloaded_base_rate, 2);
                $loaded_sec = minutesToSec($loaded_minutes);
                $load_minutes = round(($loaded_sec / 60), 5);
                $loaded_pay = round($load_minutes * $loaded_base_rate, 2);
                $total_pay = round($base_rate + $unloaded_pay + $loaded_pay, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                if ($trip["trip_format"] == 4 && $trip['wait_time_sec'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time_sec'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round(($total_pay) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }
                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);

                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }
                $total_pay_to_driver = $total_pay  + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 5 || $trip['payout_type'] == 6) {
                if ($trip['payout_type'] == 5) {
                    $payout = "No show";
                } else {
                    $payout = "Late Cancellation";
                }

                $total_miles = $unloaded_miles; // $loaded_miles +
                $flat_rate = round(($trip["flat_rate"] / 100) * $total_price, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                $total_pay = $flat_rate;
                $wait_time_sec = '';
                $wait_time_amount = 0;
                $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);
                if ($total_pay < $minimum_payout_val) {
                    if ($reimbursement_amt != 0.00) {
                        $minimum_payout_val = round(($minimum_payout_val) + $reimbursement_amt, 2);
                    }
                    if ($deduction_amt != 0.00) {
                        $minimum_payout_val = round(($minimum_payout_val) - $deduction_amt, 2);
                    }
                    $total_pay = $minimum_payout_val;
                }
                $total_pay_to_driver = $total_pay  + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } elseif ($trip['payout_type'] == 7) {
                $payout = "Mileage+Time";
                $total_miles = $unloaded_miles + $loaded_miles;
                $unloaded_pay_A = round($unloaded_miles * $unloaded_rate_per_mile, 2);
                $loaded_pay_A = round($loaded_miles * $loaded_rate_per_mile, 2);
                $total_pay_A = round($unloaded_pay_A + $loaded_pay_A, 2);
                $unloaded_sec = minutesToSec($unloaded_minutes);
                $unload_minutes = round(($unloaded_sec / 60), 5);
                $unloaded_pay_C = round($unload_minutes * $unloaded_rate_per_min, 2);
                $loaded_sec = minutesToSec($loaded_minutes);
                $load_minutes = round(($loaded_sec / 60), 5);
                $loaded_pay_C = round($load_minutes * $loaded_rate_per_min, 2);
                $total_pay_C = $unloaded_pay_C + $loaded_pay_C;
                $total_pay = $total_pay_A + $total_pay_C;
                $driver_fee = $total_miles * $insurance_rate_per_mile;
                $total_pay_to_driverA = max(($total_pay_A), 0);
                $total_pay_to_driverC = max(($total_pay_C), 0);
                $total_pay_to_driver =  round(($total_pay_to_driverA) +  ($total_pay_to_driverC), 2);
                if ($trip["trip_format"] == 4 && $trip['wait_time_sec'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time_sec'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay_to_driver = round(($total_pay_to_driver) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }

                if ($deduction_amt != 0.00) {
                    $total_pay_to_driver = ($total_pay_to_driver) - $deduction_amt;
                }

                if ($reimbursement_amt != 0.00) {
                    $total_pay_to_driver = ($total_pay_to_driver) + $reimbursement_amt;
                }

                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);

                    if ($total_pay_to_driver < $minimum_payout_val) {
                        if ($reimbursement_amt != 0.00) {
                            $minimum_payout_val = ($minimum_payout_val) + $reimbursement_amt;
                        }

                        if ($deduction_amt != 0.00) {
                            $minimum_payout_val = ($minimum_payout_val) - $deduction_amt;
                        }
                        $total_pay_to_driver = $minimum_payout_val;
                    }
                }

                $company_profitA = round($total_price - $total_pay_A, 2);
                $company_profitC = round($total_price - $total_pay_C, 2);
                $company_profit = round($company_profitA + $company_profitC, 2);
                $company_profit = round(($company_profit + $reimbursement_amt) - $deduction_amt, 2);
            } elseif ($trip['payout_type'] == 8) {
                $payout = "Base Rate Per Mile Per Min";
                $total_miles = $unloaded_miles + $loaded_miles;

                $flatrate = round(($base_rate_bmm / 100) * $total_price, 2);
                $total_loaded_cost_miles = round($loaded_miles * $loaded_rate_per_mile_bmm, 2);
                $loaded_sec = minutesToSec($loaded_minutes);
                $load_minutes = round(($loaded_sec / 60), 5);
                $total_loaded_cost_min = round($loaded_base_rate_bmm * $load_minutes, 2);

                $total_pay = round($flatrate + $total_loaded_cost_miles + $total_loaded_cost_min, 2);
                $driver_fee = $total_miles * $insurance_rate_per_mile;

                if ($trip["trip_format"] == 4 && $trip['wait_time_sec'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0) {
                    $wait_time_sec = $trip['wait_time_sec'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round(($total_pay) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = '';
                    $wait_time_amount = 0;
                }
                if ($trip["status_id"] == 3 && $trip["loaded_miles"] != '' && $trip["loaded_miles"] != '0' && $trip["loaded_minutes"] != '') {
                    $minimum_payout_val = round(($minimum_payout / 100) * $total_price, 2);
                    if ($total_pay < $minimum_payout_val) {
                        $total_pay = $minimum_payout_val;
                    }
                }
                $total_pay_to_driver = $total_pay  + $reimbursement_amt - $deduction_amt;
                $company_profit = $total_price - $total_pay + $deduction_amt - $reimbursement_amt;
            } else {
                $driver_fee = 0;
                $total_pay_to_driver = 0;
                $company_profit = 0;
                $payout = "";
                $wait_time_amount = 0;
            }

            // new code start here for change driver fees 0 if type new
            // Personal Commercial

            $res['driver_fee'] = number_format(decimal2digitNumber($driver_fee), 2, '.', '');
            $res['insurance_amount'] = number_format(decimal2digitNumber($driver_fee), 2, '.', '');
            $res['total_miles'] = $total_miles;
            $res['total_price'] = $total_price;
            $res['total_pay_to_driver'] = number_format(decimal2digitNumber($total_pay_to_driver), 2, '.', '');
            $res['company_profit'] = number_format(decimal2digitNumber($company_profit), 2, '.', '');
            $res['payout'] = $payout;
            $res['wait_time_amount'] = number_format(decimal2digitNumber($wait_time_amount), 2, '.', '');
        }

        return $res;
    }
    public function calculateDriverPayHour($driver_id, $trip_id, $given_payout_detail = array(), $paid_flag = 0)
    {
        if ($paid_flag == 1) {
            $select_query = "tr.*,dr.name,pt.id as payout_id,tr.payment_status,pt.unloaded_rate_per_mile,pt.loaded_rate_per_mile,pt.unloaded_rate_per_min,pt.loaded_rate_per_min,pt.unloaded_rate_per_hr,pt.loaded_rate_per_hr,pt.insurance_rate_per_mile,pt.base_rate,pt.unloaded_base_rate,pt.loaded_base_rate,pt.wait_time_per_hour,pt.passenger_no_show,pt.late_cancellation,pt.minimum_payout,pt.deduction_amt,pt.deduction_detail,pt.reimbursement_amt,pt.reimbursement_detail,dr.rate_updated_at,stl.period2_miles as unloaded_miles,stl.period3_miles as loaded_miles,((stl.period2)) as unloaded_minutes,((stl.period3)) as loaded_minutes,((stl.period2)) as unloaded_hours,((stl.period3)) as loaded_hours,st.status_description, stl.wait_time_sec, tr.status_id, pt.base_rate_bmm, pt.loaded_rate_per_mile_bmm, pt.loaded_base_rate_bmm,dr.hourly_rate";
        } else {
            $trip_check_county = TripMaster::select('county_type')->where('id', $trip_id)->first();
            if ($trip_check_county->county_type == 1) { //county rates
                $select_query = "tr.*,dr.name,pt.id as payout_id,tr.payment_status,pt.deduction_amt,pt.deduction_detail,pt.reimbursement_amt,pt.reimbursement_detail,dr.rate_updated_at,stl.period2_miles as unloaded_miles,stl.period3_miles as loaded_miles,((stl.period2)) as unloaded_minutes,((stl.period3)) as loaded_minutes,((stl.period2)) as unloaded_hours,((stl.period3)) as loaded_hours,st.status_description, stl.wait_time_sec, tr.status_id,sr.unloaded_rate_per_mile,sr.loaded_rate_per_mile,sr.unloaded_rate_per_min,sr.loaded_rate_per_min,sr.unloaded_rate_per_hr,sr.loaded_rate_per_hr,sr.insurance_rate_per_mile,sr.base_rate,sr.unloaded_base_rate,sr.loaded_base_rate,sr.wait_time_per_hour,sr.passenger_no_show,sr.late_cancellation,sr.minimum_payout, sr.base_rate_bmm, sr.loaded_rate_per_mile_bmm, sr.loaded_base_rate_bmm,dr.hourly_rate";
            } else { //out of county rates
                $select_query = "tr.*,dr.name,pt.id as payout_id,tr.payment_status,pt.deduction_amt,pt.deduction_detail,pt.reimbursement_amt,pt.reimbursement_detail,dr.rate_updated_at,stl.period2_miles as unloaded_miles,stl.period3_miles as loaded_miles,((stl.period2)) as unloaded_minutes,((stl.period3)) as loaded_minutes,((stl.period2)) as unloaded_hours,((stl.period3)) as loaded_hours,st.status_description, stl.wait_time_sec, tr.status_id,sr.unloaded_rate_per_mile_out as unloaded_rate_per_mile,sr.loaded_rate_per_mile_out as loaded_rate_per_mile,sr.unloaded_rate_per_min_out as unloaded_rate_per_min,sr.loaded_rate_per_min_out as loaded_rate_per_min,sr.unloaded_rate_per_hr_out as unloaded_rate_per_hr,sr.loaded_rate_per_hr_out as loaded_rate_per_hr,sr.insurance_rate_per_mile_out as insurance_rate_per_mile,sr.base_rate_out as base_rate,sr.unloaded_base_rate_out as unloaded_base_rate,sr.loaded_base_rate_out as loaded_base_rate,sr.wait_time_per_hour_out as wait_time_per_hour,sr.passenger_no_show_out as passenger_no_show,sr.late_cancellation_out as late_cancellation,sr.minimum_payout_out as minimum_payout, sr.base_rate_bmm_out as base_rate_bmm, sr.loaded_rate_per_mile_bmm_out as loaded_rate_per_mile_bmm, sr.loaded_base_rate_bmm_out as loaded_base_rate_bmm,dr.hourly_rate";
            }
        }
        $table = "trip_master_ut AS tr";
        $joinData = array(
            "trip_payout_detail AS pt" => array('joinType' => "leftJoin", 'joinTo' => "tr.id", 'joinFrom' => "pt.trip_id", 'joinTo2' => "tr.driver_id", 'joinFrom2' => "pt.driver_id"),
            "driver_service_rates AS sr" => array('joinType' => 'leftJoin', 'joinTo' => "tr.driver_id", 'joinFrom' => 'sr.driver_id', 'joinTo2' => 'tr.master_level_of_service_id', 'joinFrom2' => 'sr.level_of_service_id'),
            "driver_master_ut AS dr" => array('joinType' => "leftJoin", 'joinTo' => "tr.driver_id", 'joinFrom' => "dr.id"),
            "trip_logs AS stl" => array('joinType' => "leftJoin", 'joinTo' => "tr.id", 'joinFrom' => "stl.trip_id", 'joinTo2' => "tr.driver_id", 'joinFrom2' => "stl.driver_id"),
            "status_master_ut AS st" => array('joinType' => "leftJoin", 'joinTo' => "tr.status_id", 'joinFrom' => "st.id"),
        );
        $cond['tr.driver_id'] = $driver_id;
        $cond['tr.id'] = $trip_id;
        $where = $cond;
        $where_in_cond = array(); //['tr.status_id'] = $status_arr;
        $orderBy['tr.date_of_service'] = 'desc';
        $groupBy = array();
        $where_null_array = array();
        $trips = $this->selectjointablesGetAll($table, $select_query, $where, $joinData, $groupBy, $orderBy, 0, $where_in_cond, $where_null_array);

        $res = array();
        $res['driver_fee'] = decimal2digitNumber(0);
        $res['total_pay_to_driver'] = decimal2digitNumber(0);
        $res['company_profit'] = decimal2digitNumber(0);
        $res['payout'] = '';
        $res['wait_time_amount'] = decimal2digitNumber(0);

        //check commisson rate

        foreach ($trips as $trip123) {
            $trip = (array) $trip123;
            if ($given_payout_detail != null) {
                $trip["hourly_rate"] = $given_payout_detail['hourly_rate'];
            }
            $total_price = $trip["total_price"];
            if ($total_price == '') {
                $total_price = 0;
            }

            $unloaded_rate_per_mile = $trip["unloaded_rate_per_mile"];
            $loaded_rate_per_mile = $trip["loaded_rate_per_mile"];

            $driver_details = DriverMaster::where('id', $driver_id)->first();
            if ($driver_details->insurance_type == 'Personal Commercial') {
                $insurance_rate_per_mile = 0;
            } else {
                $insurance_rate_per_mile = $trip["insurance_rate_per_mile"];
            }

            $wait_time_per_hour = $trip["wait_time_per_hour"];
            $minimum_payout = $trip['minimum_payout'];
            $deduction_amt = $trip['deduction_amt'];
            $deduction_detail = $trip['deduction_detail'];
            $reimbursement_amt = $trip['reimbursement_amt'];

            if ($trip["unloaded_miles"] == '') {
                $unloaded_miles = 0;
            } else {
                $unloaded_miles = $trip["unloaded_miles"];
            }

            if ($trip["loaded_miles"] == '') {
                $loaded_miles = 0;
            } else {
                $loaded_miles = $trip["loaded_miles"];
            }

            if ($trip["unloaded_minutes"] == '') {
                $unloaded_minutes = 0;
            } else {
                $unloaded_minutes = $trip["unloaded_minutes"];
            }

            if ($trip["loaded_minutes"] == '') {
                $loaded_minutes = 0;
            } else {
                $loaded_minutes = $trip["loaded_minutes"];
            }
            if ($trip["trip_format"] == 4) {
                if ($trip["wait_time_sec"] != '' && $trip["wait_time_sec"] != '0') {
                    $wait_time = secondToTimes($trip["wait_time_sec"]);
                } else {
                    $wait_time = '0';
                }
            } else {
                $wait_time = 'NA';
            }
            /////////
            $res['unloaded_miles'] = $unloaded_miles;
            $res['unloaded_minutes'] = $unloaded_minutes;
            $res['loaded_miles'] = $loaded_miles;
            $res['loaded_minutes'] = $loaded_minutes;
            $res['wait_time'] = $wait_time;
            /////////

            $loaded_secs = strtotime($loaded_minutes) - strtotime("00:00:00");
            if ($loaded_secs < 0) {
                $loaded_secs = 0;
            }
            $loaded_hourly_rate = ($trip["hourly_rate"] / 3600) * $loaded_secs;
            $unloaded_sec = strtotime($unloaded_minutes) - strtotime("00:00:00");
            // return $unloaded_sec;
            if ($unloaded_sec < 0) {
                $unloaded_sec = 0;
            }
            $unloaded_hourly_rate = ($trip["hourly_rate"] / 3600) * $unloaded_sec;

            $total_miles = $unloaded_miles + $loaded_miles;
            $unloaded_pay = $unloaded_miles * $unloaded_rate_per_mile;
            ///////////////////////////////////////////////

            ////////////////////////////////////////////
            $total_pay = round($loaded_hourly_rate + $unloaded_hourly_rate, 2); //$unloaded_pay + $loaded_pay;
            $driver_fee = round($total_miles * $insurance_rate_per_mile, 2);

            if ($trip["trip_format"] == 4) {
                // && $trip['wait_time_sec'] > 0 && $wait_time_per_hour != '' && $wait_time_per_hour > 0
                if ($trip["wait_time_sec"] != '' && $trip["wait_time_sec"] != '0') {
                    $wait_time_sec = $trip['wait_time_sec'];
                    $wait_time_amount = calculateWaitimeAmount($wait_time_sec, $wait_time_per_hour);
                    $total_pay = round(($total_pay) + $wait_time_amount, 2);
                } else {
                    $wait_time_sec = 0;
                    $wait_time_amount = 0;
                }
            } else {
                $wait_time_sec = 0;
                $wait_time_amount = 0;
            }
            ///////////////////////////////

            //////////////////////////////
            $total_pay_to_driver = $total_pay - $driver_fee + $reimbursement_amt - $deduction_amt;
            $company_profit = $total_pay_to_driver - $total_price;
            // payout end here
            if ($wait_time_sec < 0) {
                $wait_time_sec = 0;
            }
            if ($unloaded_sec < 0) {
                $unloaded_sec = 0;
            }
            if ($loaded_secs < 0) {
                $loaded_secs = 0;
            }
            $total_count = $wait_time_sec + $unloaded_sec + $loaded_secs;
            $res['payout'] = 'Hour';
            $res['insurance_amount'] = number_format(decimal2digitNumber($driver_fee), 2, '.', '');
            $res['total_miles'] =  $total_miles;
            $res['wait_time_sec'] =  secondToTimes($wait_time_sec);
            $res['unloaded_secs'] =   secondToTimes($unloaded_sec); //. '------------sec=' . $unloaded_secs . '--------minutes = ' . $unloaded_minutes; //$unloaded_minutes;
            $res['loaded_secs'] =  secondToTimes($loaded_secs);
            $res['total_duration'] =  secondToTimes($total_count);
            ///////////////////////////
            $res['total_price'] = $total_price;
            $res['driver_fee'] = number_format(decimal2digitNumber($driver_fee), 2, '.', '');
            $res['total_pay_hourly'] = number_format(decimal2digitNumber($total_pay), 2, '.', '');
            $temp_pay = decimal2digitNumber(($total_pay_to_driver < 0 ? 0 :  $total_pay_to_driver));
            $res['total_pay_to_driver'] = number_format($temp_pay, 2, '.', '');
            $res['company_profit'] = number_format(decimal2digitNumber($company_profit), 2, '.', '');
            // $res['payout'] = $payout;
            $res['wait_time_amount'] = number_format(decimal2digitNumber($wait_time_amount), 2, '.', '');
        }
        return $res;
    }
    public static function selectjointablesAll($table, $select_query, $where, $joinData, $groupBy = null, $orderBy = null, $where_in_cond = array(), $like_array = array(), $groupcond = array(), $start_date = null, $end_date = null, $where_null_array = array(), $postData = array(), $search_payor_types = array())
    {
        $returndata = DB::table($table);
        if (count($joinData) > 0) {
            foreach ($joinData as $key => $val) {
                $table = $key;
                $joinType = $val['joinType'];
                $returndata =  $returndata->$joinType($table, function ($join) use ($val) {
                    $joinTo = $val['joinTo'];
                    $joinFrom = $val['joinFrom'];
                    $join->on($joinTo, '=', $joinFrom);
                    if (isset($val['joinTo2']) && isset($val['joinTo2'])) {
                        $joinTo2 = $val['joinTo2'];
                        $joinFrom2 = $val['joinFrom2'];
                        $join->on($joinTo2, '=', $joinFrom2);
                    }
                });
            }
        }
        $returndata = $returndata->select(DB::raw($select_query));
        $returndata = $returndata->where($where);

        foreach ($where_in_cond as $key => $val) {
            $returndata =  $returndata->whereIn($key, $val);
        }

        foreach ($groupcond as $key => $val) {
            $returndata =  $returndata->whereRaw($val);
        }

        foreach ($where_null_array as  $val) {
            $returndata =  $returndata->whereNull($val);
        }

        if (count($search_payor_types) > 0) {
            $returndata->where(function ($query) use ($search_payor_types, $postData) {
                foreach ($search_payor_types as $key => $type) {
                    if ($key == 0) {
                        $query->Where('tr.payor_type', $type);
                        if ($type == 1 && isset($postData["search_member_name"]) && $postData["search_member_name"] && count($postData["search_member_name"])) {
                            $query->whereIn('tr.payor_id', $postData["search_member_name"]);
                        } elseif ($type != 1 && $type != 3 && isset($postData["search_facility_name"]) && $postData["search_facility_name"] && count($postData["search_facility_name"])) {
                            $query->whereIn('tr.payor_id', $postData["search_facility_name"]);
                        } elseif ($type == 3 && isset($postData["provtype"]) && $postData["provtype"] && count($postData["provtype"])) {
                            $query->whereIn('tr.payor_id', $postData["provtype"]);
                        }
                    } else {
                        $query->orWhere('tr.payor_type', $type);
                        if ($type == 1 && isset($postData["search_member_name"]) && $postData["search_member_name"] && count($postData["search_member_name"])) {
                            $query->whereIn('tr.payor_id', $postData["search_member_name"]);
                        } elseif ($type != 1 && $type != 3 && isset($postData["search_facility_name"]) && $postData["search_facility_name"] && count($postData["search_facility_name"])) {
                            $query->whereIn('tr.payor_id', $postData["search_facility_name"]);
                        } elseif ($type == 3 && isset($postData["provtype"]) && $postData["provtype"] && count($postData["provtype"])) {
                            $query->whereIn('tr.payor_id', $postData["provtype"]);
                        }
                    }
                }
            });
        }

        ////like array
        if (count($like_array) > 0) {
            $returndata = $returndata->where(function ($query) use ($like_array) {
                // $query->where('trip_master_ut.date_of_service', '>=', $start_date);
                $i = 0;
                foreach ($like_array as $k => $v) {
                    if ($i == 0) {
                        $query->where($k, 'LIKE', '%' . $v . '%');
                    }
                    $query->orWhere($k, 'LIKE', '%' . $v . '%');
                    $i++;
                }
            });
        }

        // return $groupBy;
        if ($groupBy != null) {
            if (count($groupBy) > 0) {
                foreach ($groupBy as $key => $val) {
                    // return $val;
                    $returndata =  $returndata->groupBy($val);
                }
            }
        }

        if ($orderBy != null) {
            if (count($orderBy) > 0) {
                foreach ($orderBy as $key => $val) {
                    $returndata =  $returndata->orderBy($key, $val);
                }
            }
        }

        if ($start_date != null && $end_date != null && $start_date != 'null' && $end_date != 'null') {
            $start_date = Carbon::parse($start_date, auth()->user()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));

            $end_date = Carbon::parse($end_date, auth()->user()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));

            $returndata =  $returndata
                ->whereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) >="' . $start_date . '"')
                ->WhereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) <="' . $end_date . '"');
        } elseif ($start_date != null && $start_date != 'null') {
            $start_date = $start_date;
            $start_date = Carbon::parse($start_date, auth()->user()->timezone)
                ->startOfDay()
                ->setTimezone(config('app.timezone'));

            $returndata =  $returndata->whereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) >="' . $start_date . '"');
        } elseif ($end_date != null && $end_date != 'null') {
            $end_date = $end_date;
            $end_date = Carbon::parse($end_date, auth()->user()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            // dd($end_date->toDateTimeString());
            $returndata =  $returndata->whereRaw('concat(tr.date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE tr.shedule_pickup_time END) <="' . $end_date . '"');
        }
        $returndata =  $returndata->get()->toArray();
        return $returndata;
    }
}
