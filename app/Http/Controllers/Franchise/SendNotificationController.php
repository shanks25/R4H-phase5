<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Logics\Logics;
use App\Models\DriverMaster;
use App\Models\DriverNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SendNotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'driver_id' => 'required|numeric',
                'title' => 'required',
                'message' => 'required',
            ]);
            if ($validator->fails()) {
                return metaData(false, $request, '2027', '', $error_code = 400, '', $validator->messages());
            }
            $driver_id = $request->driver_id;
            $isVallid = Logics::isDriverValid($driver_id, $request->eso_id);
            if (!$isVallid) {
                return metaData(false, $request, '2027', '', $error_code = 400, '', 'Invalid Driver_id');
            }

            $driver = DriverMaster::select('id', 'device_token')
                ->where('id', $request->driver_id)
                ->first();
            $body = $request->message;
            $insert_arr = array(
                "driver_id" => $driver_id,
                "post_by" => eso()->id,
                "notification" => $body,
                "is_read" => 0
            );
            DriverNotification::create($insert_arr);
            $badge = Drivernotification::where('driver_id', $driver_id)
                ->where('is_read', 0)
                ->get()
                ->count();
            // $device_token, $body, $title, $badge = '', $subtitle = ''
            $device_token = $driver->device_token;
            $title = $request->title;
            $response = Logics::sendPushNotification($device_token, $body, $title, $badge);
            $response = json_decode($response);
            $successData['success'] = $response->success;
            $successData['results'] = $response->results;
            $dataMeta['data'] = $successData;
            $metaData = metaData(true, $request, '2026');
            return $new_merge = merge($dataMeta, $metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 2027, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
