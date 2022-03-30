<?php

namespace App\Http\Controllers;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Member;
use GuzzleHttp\Client;
use Carbon\CarbonPeriod;
use App\Models\TripMaster;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use App\Models\TripRecurringMaster;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rules\Unique;

class PlayController extends Controller
{
    public function index(Request $request)
    {
        return Carbon::parse("2021-04-08T09:30:00.000-05:00")->format('Y-m-d H:i');
    }

    public function index2()
    {
        $pick = 'Deron Heights, Baner Road, opp. hotel mahabaleshwar, Varsha Park Society, Baner, Pune, Maharashtra';
        $drop = 'Phase 3, Hinjewadi Rajiv Gandhi Infotech Park, Hinjawadi, Pune, Maharashtra 411057';
        return   googleMatrix($pick, $drop);
  
        //   return $result;
        $trip  = [];
        return array_key_exists('pickup_time', $trip) ? 1 : '0';
        //  AIzaSyBNvpByWN1GA0-4ZOKzPOlBoYuHL4d5ypM

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/distancematrix/json?");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "destinations=San%20Francisco&origins=849VCWC8%2BR9&key=AIzaSyBNvpByWN1GA0-4ZOKzPOlBoYuHL4d5ypM");

        // In real life you should use something like:
        // curl_setopt($ch, CURLOPT_POSTFIELDS,
//          http_build_query(array('postvar1' => 'value1')));

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        // Further processing ...
        if ($server_output == "OK") {
            return json_decode($server_output);
        } else {
            return json_decode($server_output);
        }


        // return $response ;
    }
}
