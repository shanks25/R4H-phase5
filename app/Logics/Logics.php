<?php

namespace App\Logics;

use App\Models\DriverLeaveDetail;
use App\Models\DriverMaster;
use App\Models\StatusMaster;
use App\Models\TripMaster;

class Logics
{
    public static function getGoogleDirectionDuration($origin, $destination)
    {
        $apiKey = getGoogleKeyApi(eso()->id); //env('googleMapApiKey');
        $apiUrl = env('googleDirectionUrl');
        $url = $apiUrl . '?' . 'origin=' . urlencode($origin) . '&destination=' . urlencode($destination) . '&key=' . $apiKey;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curl);
        if (curl_errno($curl)) {
            throw new \Exception(curl_error($curl));
        }
        curl_close($curl);

        $json = json_decode(trim($res), true);

        // return $json['routes'];
        $totalDistance = 0;
        if (isset($json['routes'][0])) {
            //Automatically select the first route that Google gave us.
            $route = $json['routes'][0];
            //Loop through the "legs" in our route and add up the distances.

            foreach ($route['legs'] as $leg) {
                $totalDistance = $totalDistance + (float) str_replace(',', '', $leg['distance']['text']);
                $totalduration = $leg['duration']['value'];

                $pickup_lat = $leg['start_location']['lat'];
                $pickup_lng = $leg['start_location']['lng'];

                $dropoff_lat = $leg['end_location']['lat'];
                $dropoff_lng = $leg['end_location']['lng'];
            }

            return array('totalDistance' => $totalDistance, 'totalduration' => $totalduration, 'pickup_lat' => $pickup_lat, 'pickup_lng' => $pickup_lng, 'dropoff_lat' => $dropoff_lat, 'dropoff_lng' => $dropoff_lng);
        }

        return array('totalDistance' => 0, 'totalduration' => 0, 'pickup_lat' => '', 'pickup_lng' => '', 'dropoff_lat' => '', 'dropoff_lng' => '');
    }
    public static function isTripIdsVallidForEso($trip_ids, $eso_id)
    {
        /* This is checking if the trip_ids is valid for the eso_id. */
        $trips =  TripMaster::whereIn('id', $trip_ids)
            ->where('user_id', '!=', $eso_id)
            ->first();
        if ($trips) {
            return false; //not valid
        } else {
            return true; // vallid
        }
    }
    public static function isDriverValid($driver_id, $eso_id)
    {
        /* This is checking if the driver_id is valid for the eso_id. */
        $driver =  DriverMaster::select('id')
            ->where('user_id', $eso_id)
            ->where('id', $driver_id)
            ->first();
        if ($driver) {
            return true; //valid
        } else {
            return false; // not vallid
        }
    }
    public static function isTripValid($trip_id, $eso_id)
    {
        /* This is checking if the trip_id is valid for the eso_id. */
        $trip =  TripMaster::select('id')
            ->where('user_id', $eso_id)
            ->where('id', $trip_id)
            ->first();
        if ($trip) {
            return true; //valid
        } else {
            return false; // not vallid
        }
    }
    public static function isStatusArrayVallid($statusArray)
    {
        /* This is checking if the statusArray is valid. */
        $status = StatusMaster::select('id')->get()->toArray();
        if ($status) {
            $all_ids = array_column($status, 'id');
            foreach ($statusArray as $currentStatus) {
                if (!in_array($currentStatus, $all_ids)) {
                    return false; //not vallid
                    break;
                }
            }
        }
        return true; //vallid

    }
    public static function isDateValid($date)
    {
        $day = date('d', strtotime($date));
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));
        $valid = checkdate($month, $day, $year);
        if ($valid) {
            return true;
        } else {
            return false;
        }
    }
    public static function isDriverIdsValid($driver_ids, $eso_id)
    {
        /* This is checking if the driver_id is valid for the eso_id. */
        $driver =  DriverMaster::select('id')
            ->where('user_id', '!=', $eso_id)
            ->whereIn('id', $driver_ids)
            ->first();
        if ($driver) {
            return false; //not valid
        } else {
            return true; //  vallid
        }
    }
    public static function DriverUtilization($driver_seconds)
    {
        $total_seconds = 28800; //86400= 24 hrs, 28800 = 8 hrs
        return round(($driver_seconds / $total_seconds) * 100);
    }
    public static function vehicleUtilization($total_revenue)
    {
        $total_amount = 500; //500 is target
        return  round(($total_revenue / $total_amount) * 100);
    }
    public static function sendPushNotification($device_token, $body, $title, $badge = '', $subtitle = '')
    {
        $url = env('FIREBASEURL');
        $api_key = env('FIREBASEKEY'); //config('services.fairebasekey');

        $fields = array(
            'registration_ids' => array(
                $device_token
            ),
            'data' => array(
                "title" => $title,
                "body" => $body,
                "badge" => $badge,
                "subtitle"  => $subtitle
            ),

        );
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $api_key
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === false) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
}
