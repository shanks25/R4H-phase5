<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\BaseLocation;
use App\Models\TripDeleteLog;
use App\Models\TripMaster;
use App\Traits\TripTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TripController extends Controller
{
    use TripTrait;
    public function view(Request $request) //20002
    {
        try {
            if ($request->trip_id == '') {
                return metaData(false, $request, 20002, '', 401, '', 'trip_id is required');
            }
            return $this->tripSingle($request);
        } catch (\Exception $e) {
            return metaData(false, $request, 20002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function updateTripStatus(Request $request)
    {
        try {
            $trip_ids = array();
            if ($request->filled('trip_ids')) {
                $trip_ids =  json_decode($request->trip_ids, true);
            } else {
                return metaData(false, $request, 20005, '', 400, '', 'trip_ids is required');
            }
            if (!$request->filled('status_id')) {
                return metaData(false, $request, 20005, '', 400, '', 'status_id is required');
            }
            $status = $request->status_id;
            if (!in_array($status, [2, 3, 5, 6, 9, 8, 11])) {
                return metaData(false, $request, 20005, '', 400, '', 'status_id not allowed');
            }
            if (count($trip_ids) >  0) {
                $arrUpdate = array();
                $arrNotUpdate = array();
                $arrNotUpdatePayStatus = array();
                $arrCompTripNotMove = array();
                $arrfuturedTripNotMove = array();
                $incorrect_status_cout = 0;
                foreach ($trip_ids as $trip_id) {
                    $isupdate = 0;
                    $trip_is_inprogress = 1;

                    $trip = TripMaster::select('id', 'date_of_service', 'status_id', 'payment_status', 'driver_id')->where('id', $trip_id)->first();

                    if ($trip) {
                        if ($trip->date_of_service == "0000-00-00" || $trip->date_of_service == null) {
                            $dateofservice = 'NA';
                        } else {
                            $dateofservice = modifyTripTime($trip->date_of_service, $trip->shedule_pickup_time);
                        }

                        $isFutureTrip = 1;
                        $eso_time = Carbon::now()->timezone(eso()->timezone);
                        if ($dateofservice != 'NA' && strtotime($dateofservice) <= strtotime(date('Y-m-d', strtotime($eso_time)))) {
                            $isFutureTrip = 0;
                        }
                        // check status is same
                        if ($trip->status_id == $status) {
                            $incorrect_status_cout++;
                            continue;
                        } else {
                            if ($status == 2) {
                                $confirm_status = '0';
                            } elseif ($status == 3) {
                                $confirm_status = '9';
                            } elseif ($status == 5) {
                                $confirm_status = '7';
                            } elseif ($status == 6) {
                                $confirm_status = '8';
                            } elseif ($status == 8) {
                                $confirm_status = '0';
                            } elseif ($status == 9) {
                                $confirm_status = '2';
                            } else {
                                $confirm_status = '0';
                            }

                            if ($status != '2' && $isFutureTrip == 1) {
                                $trip_is_inprogress = 0;
                                $arrfuturedTripNotMove[] = $trip_id;
                            } elseif ($status == 2 && $trip['status_id'] == '3') {
                                $trip_is_inprogress = 0;
                                $arrCompTripNotMove[] = $trip_id;
                            } else {
                                $trip_is_inprogress = 0;
                                $update_arr = array("status_id" => $status, "confirm_status" => $confirm_status);

                                if ($status == 5 || $status == 6) {
                                    $payout_type = null;
                                    $update_arr['payout_type'] = $payout_type;
                                }

                                if ($status == 3) {
                                    $payout_type = 1;
                                    $update_arr['payout_type'] = $payout_type;
                                }

                                if ($status == 2) {
                                    $update_arr['driver_id'] = null;
                                    $update_arr['vehicle_id'] = null;
                                }
                                $isupdate = false;
                                if ($trip['payment_status'] != 0 && $trip[0]['payment_status'] != null) {
                                    $arrNotUpdatePayStatus[] = $trip_id;
                                } else {
                                    $isupdate = TripMaster::where('id', $trip_id)->update($update_arr);
                                }


                                if ($status == 3 || $status == 5 || $status == 6) {
                                    // updatetripsprofit($trip_id);
                                }
                                // checklogsgenerate($trip_id, $trip->driver_id);
                            }
                        }

                        if ($isupdate == true) {
                            $arrUpdate[] = $trip_id;
                        } elseif ($trip_is_inprogress == 1) {
                            $arrNotUpdate[] = $trip_id;
                        }
                    } else {
                        $arrNotUpdate[] = $trip_id;
                    }
                }
                $data_array = [
                    "updateCount" => count($arrUpdate), "notUpdateCount" => count($arrNotUpdate), "paymentStatusNotUpdate" => count($arrNotUpdatePayStatus),
                    "incorrect_status_count" => $incorrect_status_cout, 'completedTripNotUpdateCount' => count($arrCompTripNotMove), 'futureTripNotUpdateCount' => count($arrfuturedTripNotMove)
                ];
                $convert_array = ['data' => $data_array];
                $merged_array =  merge($convert_array, metaData(true, $request, 20005, 'success', 200, '', ''));
                return response()->json($merged_array);
            } else {
                return metaData(false, $request, 20005, '', 502, '', 'Trip Id not found ');
            }
            // $status = $request->move_status;
            // $arr = explode(",", $postData["move_arr"]);
        } catch (\Exception $e) {
            return metaData(false, $request, 20002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function updateBaseLocation(Request $request)
    {
        try {
            $trip_ids = array();
            if ($request->filled('trip_ids')) {
                $trip_ids =  json_decode($request->trip_ids, true);
            } else {
                return metaData(false, $request, 20006, '', 400, '', 'trip_ids is required');
            }
            if (!$request->filled('baselocation_id')) {
                return metaData(false, $request, 20006, '', 400, '', 'baselocation_id is required');
            }
            $baselocation_id = $request->baselocation_id;
            $arrUpdate = array();
            $arrNotUpdate = array();
            if (count($trip_ids) > 0) {
                foreach ($trip_ids as $trip_id) {
                    $trip = TripMaster::select('status_id', 'pickup_address')->where('id', $trip_id)->first();
                    if ($trip) {
                        $status_id = $trip['status_id'];
                        $origin = $trip['pickup_address'];
                        $base_location = BaseLocation::where('id', $baselocation_id)->first(); //$this->trip->getBaseLocationsName($baselocation);
                        $destination = $base_location['address'];
                        // $distance = $this->get_google_direction($origin, $destination);
                        $isupdate = 0;
                        if ($status_id == 2) {
                            $update_arr = array("base_location_id" => $baselocation_id);
                            // $update_arr = array("base_location_id" => $baselocation_id, );
                            //
                            $isupdate = TripMaster::where('id', $trip_id)->update($update_arr);
                        }
                        //echo $this->db->last_query();exit;
                        if ($isupdate == true) {
                            $arrUpdate[] = $trip_id;
                        } else {
                            $arrNotUpdate[] = $trip_id;
                        }
                    } else {
                        $arrNotUpdate[] = $trip_id;
                    }
                }
                $data_array = ["updateCount" => count($arrUpdate), "notUpdateCount" => count($arrNotUpdate)];
                // return ['data' => $data_array];
                $convert_array = ['data' => $data_array];
                $merged_array =  merge($convert_array, metaData(true, $request, 20006, 'success', 200, '', ''));
                return response()->json($merged_array);
            } else {
                return metaData(false, $request, 20005, '', 400, '', 'Trip Id not found ');
            }
        } catch (\Exception $e) {
            return metaData(false, $request, 20006, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function Bulkdestroy(Request $request)
    {
        try {
            $trip_ids = array();
            if ($request->filled('trip_ids')) {
                $trip_ids =  json_decode($request->trip_ids, true);
            } else {
                return metaData(false, $request, 20006, '', 400, '', 'trip_ids is required');
            }
            if (count($trip_ids) > 0) {
                $trips = TripMaster::whereIn('id', $trip_ids)->get();
                $filtered = $trips->filter(function ($trip, $key) {
                    if ($trip->status_id == 1 || $trip->status_id == 2 || $trip->status_id == 7) {
                        return $trip;
                    }
                });

                $filter_count = $filtered->count();
                if (!$filtered->count()) {
                    return metaData(false, $request, 20006, '', 400, '', 'Select Assigned / Unassigned trips only ');
                }

                $TripID = array();
                foreach ($filtered as $key => $trip) {
                    $trip->delete();
                    $TripID[] = $trip->id;
                }

                if ($TripID) {
                    // if (eso()->guard('admin')->user()) { //admin
                    //     $master_user = 'Yes';
                    // } else {
                    $master_user = 'No';
                    // }
                    $insert_arr = array("user_id" => eso()->id, "user_ip" =>  $request->ip(), "TripID" => implode(',', $TripID), "master_user" => $master_user);
                    TripDeleteLog::create($insert_arr);
                }

                if ($trips->count() != $filter_count) {
                    $custom_array = ['deletedCount' => $filter_count];
                    $convert_array = ['data' => $custom_array];
                    $merged_array =  merge($convert_array, metaData(false, $request, 20006, '', 502, '', $filter_count . ' Assigned / Unassigned trips deleted successfully. Trips with other statuses cannot be deleted.'));
                    return response()->json($merged_array);
                }
                $custom_array = ['deletedCount' => $filter_count];
                $convert_array = ['data' => $custom_array];
                $successs_msg = $filter_count . ' Trips deleted successfully.';
                $merged_array =  merge($convert_array, metaData(true, $request, 20006, $successs_msg, 502, '', $filter_count . ''));
                return response()->json($merged_array);
            } else {
                return metaData(false, $request, 20006, '', 400, '', 'trip_ids not found ');
            }
        } catch (\Exception $e) {
            return metaData(false, $request, 20006, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
