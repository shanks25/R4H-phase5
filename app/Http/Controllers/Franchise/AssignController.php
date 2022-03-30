<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Logics\Logics;
use App\Models\DriverMaster;
use App\Models\TripMaster;
use App\Models\TripPayoutDetail;
use App\Models\Vehicle;
use App\Traits\DriverTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Null_;

class AssignController extends Controller
{
    use DriverTrait;
    public function assignBulk(Request $request) //20008
    {
        try {
            $trip_ids = array();
            if ($request->filled('trip_ids')) {
                $trip_ids =  json_decode($request->trip_ids, true);
            } else {
                return metaData(false, $request, 20008, '', 400, '', 'trip_ids is required');
            }
            if (!$request->filled('driver_id')) {
                return metaData(false, $request, 20008, '', 400, '', 'driver_id is required');
            }
            if (!$request->filled('assign_flag')) {
                return metaData(false, $request, 20008, '', 400, '', 'assign_flag is required');
            }
            $driver_id = $request->driver_id;
            $assign_flag = $request->assign_flag; //1 assign ,2 reassign
            $arrUpdate = array();
            $arrNotUpdate = array();

            $driver_detail = DriverMaster::where('id', $driver_id)->first();
            $vehicle_id = $driver_detail->vehicle_id;
            $notification_flag = 1; //1 for send notifivcation

            if ($vehicle_id == null) {
                return metaData(false, $request, 20008, '', 400, '', 'Please assign vehicle to driver first.');
            }

            $vehicle_detail = Vehicle::where('id', $vehicle_id)->where('status', '1')->first();
            if (!$vehicle_detail) {
                return metaData(false, $request, 20008, '', 400, '', 'Please activate driver vehicle.');
            }
            if (count($trip_ids) > 0) {
                foreach ($trip_ids as $trip_id) {
                    $trip = TripMaster::select('status_id', 'date_of_service', 'appointment_time', 'shedule_pickup_time', 'driver_id')->where('id', $trip_id)->first();
                    if ($trip) {
                        if ($trip->date_of_service == null || $trip->date_of_service == '') {
                            $collectArrNotUpdate['trip_id'] = $trip_id;
                            $collectArrNotUpdate['reason'] = 'The trip details are empty, please fill out all required fields.';
                            $arrNotUpdate[] = $collectArrNotUpdate;
                            continue;
                        }
                        $check = $this->checkDriverIsValidAssignTrip($driver_id, $trip->date_of_service);
                        if ($check['valid'] == 0) {
                            $collectArrNotUpdate['trip_id'] = $trip_id;
                            $collectArrNotUpdate['reason'] = $check['msg'];
                            $arrNotUpdate[] = $collectArrNotUpdate;
                            continue;
                        }

                        $status_id = $trip->status_id;
                        if ($status_id != 3 && $status_id != 4 && $status_id != 5 && $status_id != 6 && $status_id != 10 && $status_id != 11 && $status_id != 12) {
                            if ($vehicle_id != null && $vehicle_id != '' && $vehicle_id != '0') {
                                $assignDriver =  $this->assignLogic($driver_id, $trip_id, $assign_flag, $notification_flag);
                                if ($assignDriver == 1) {
                                    $arrUpdate[] = $trip_id;
                                } else {
                                    $collectArrNotUpdate['trip_id'] = $trip_id;
                                    $collectArrNotUpdate['reason'] = 'Trip not assign ';
                                    $arrNotUpdate[] = $collectArrNotUpdate;
                                }
                            } else {
                                $collectArrNotUpdate['trip_id'] = $trip_id;
                                $collectArrNotUpdate['reason'] = 'Vehicle is not assign to Driver  ';
                                $arrNotUpdate[] = $collectArrNotUpdate;
                            }
                        }
                    } else {
                        $collectArrNotUpdate['trip_id'] = $trip_id;
                        $collectArrNotUpdate['reason'] = 'Trip id not found';
                        $arrNotUpdate[] = $collectArrNotUpdate;
                    }
                }
                $dataReturn['updatedCount'] = count($arrUpdate);
                $dataReturn['notUpdateTripCount'] = count($arrNotUpdate);
                $dataReturn['notUpdateTripDetails'] = $arrNotUpdate;

                $convert_array = ['data' => $dataReturn];
                return  $merged_array =  merge($convert_array, metaData(true, $request, 20008, 'success', 200, '', ''));
            } else {
                return metaData(false, $request, 20008, '', 400, '', 'trip_ids not found');
            }
        } catch (\Exception $e) {
            return metaData(false, $request, 20008, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    public function unAssignBulk(Request $request) //2018
    {
        try {
            $validator = Validator::make($request->all(), [
                'trip_ids' => 'required|array',
            ]);
            if ($validator->fails()) {
                return   metaData(false, $request, '2018', '', $error_code = 400, '', $validator->messages());
            }

            $isValidTrips =  Logics::isTripIdsVallidForEso($request->trip_ids, $request->eso_id);
            if (!$isValidTrips) {
                return   metaData(false, $request, '2018', '', $error_code = 400, '', "Invalid trip_ids");
            }
            $trips = TripMaster::select('id')
                ->whereNotIn('status_id', [1, 7]) // 1assign ,2 reassign
                ->whereIn('id', $request->trip_ids)
                ->get();
            if (count($trips) > 0) {
                return   metaData(false, $request, '2018', '', $error_code = 400, '', "Please select Assigned or Reassigned trip's only ");
            }

            $setData['status_id'] = 2; // unassign
            $setData['confirm_status'] = '0'; // unassign
            $setData['payout_type'] = 1; // mileage
            $setData['driver_id'] = Null;
            TripPayoutDetail::whereIn('trip_id', $request->trip_ids)->delete();
            $trip_count = TripMaster::whereIn('id', $request->trip_ids)->update($setData);
            $data_array = [
                "updateCount" => $trip_count
            ];
            $convert_array = ['data' => $data_array];
            return   merge($convert_array, metaData(true, $request, 2018, 'success', 200, '', ''));
        } catch (\Exception $e) {
            return metaData(false, $request, 2018, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
