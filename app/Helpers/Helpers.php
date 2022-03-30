<?php

use Carbon\Carbon;
use App\Models\User;
use App\Mail\CommonMail;
use App\Models\Accident;
use App\Models\TripMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\DriverRequestFormFinalExamQuestionAnswer;
use App\Models\VehicleMaintenance;

function upload($file, $path)
{
    return   Storage::putFile($path, $file);
}


//generate Unique Ticket NUmber
function generateUniqueNumber()
{
    $number = mt_rand(1000000, 9999999); // better than rand()
    // call the same function if the barcode exists already
    $exist = VehicleMaintenance::where('ticket_id', $number)->exists();
    if ($exist) {
        return generateUniqueNumber();
    }
    // otherwise, it's valid and can be used
    return $number;
}
function eso($id = '')
{
    if (!$id) {
        $id = request()->eso_id;
    }
    return User::find(request()->eso_id);
}

function errorDesc($e)
{
    return $e->getMessage() . ' ' . $e->getLine() . ' ' . $e->getFile();
}

function metaData($status = true, $request, $function_code = '', $success_msg = 'success', $error_code = '', $exception = '', $error_message = '')
{
    $data = [
        'status' => $status,
        'success_message' => $success_msg,
        'error_message' => $error_message,
        'error_exception' => $exception,
        'error_code' => $error_code,
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'function_code' => $function_code,
    ];
    if (isset($request->total_price_sum)) {
        $data['total_price_sum'] = $request->total_price_sum;
    }
    return $data;
}

function authTimeZone()
{
    $user = eso();
    return $user->timezone;
}

function merge($array1, $array2)
{
    return  array_merge($array1, $array2);
}

function modifyTripTime($date, $time = '')
{
    $n = \Carbon\Carbon::parse($date . ' ' . $time, config('app.timezone'));
    if ($user = eso()) {
        return $n->setTimezone($user->timezone)->format('H:i:s');
    }
    return $n;
}

function modifyTripDate($date, $time = '')
{
    $n = \Carbon\Carbon::parse($date . ' ' . $time, config('app.timezone'));
    if ($user = eso()) {
        return $n->setTimezone($user->timezone)->format('Y-m-d');
    }
    return $n;
}
function profitMargin($total_trip_profit, $total_trip_cost)
{
    $val = ($total_trip_profit * 100) / $total_trip_cost;
    return number_format($val, 2);
}

function accidentCount($date)
{
    $user_timezone_offset = Carbon::now()->timezone(eso()->timezone)->getOffsetString();
    $app_timezone_offset  = Carbon::now()->getOffsetString();
    $accident =  Accident::select(
        DB::raw('DATE(CONVERT_TZ(concat(date," ", CASE WHEN time IS NULL THEN "00:00:00" ELSE time END),"' . $app_timezone_offset . '","' . $user_timezone_offset . '")) as date'),
        DB::raw('count(*) as accident_count'),
    )->groupBy('date')->get();

    $accident_count = 0;
    $data = $accident->where('date', $date)->first();
    if ($data) {
        $accident_count = $data->accident_count;
    }
    return $accident_count;
}

function modifyTripTimezone($date, $time = '')
{
    $n = \Carbon\Carbon::parse($date . ' ' . $time, config('app.timezone'));
    if ($user = eso()) {
        return $n->setTimezone($user->timezone);
    }
    return $n;
}
function modifyDriverLogTime($dateTime, $timezone)
{
    $n = \Carbon\Carbon::parse($dateTime, $timezone);
    $user = eso();
    return $n->setTimezone($user->timezone);
}
function getTimezone($datetime, $to_timezone)
{
    return  $n = \Carbon\Carbon::parse($datetime, $to_timezone);
}
function modifyTripWithDateTime($datetime)
{
    $n = \Carbon\Carbon::parse($datetime, config('app.timezone'));
    if ($user = eso()) {
        return $n->setTimezone($user->timezone);
    }
    return $n;
}
function searchStartDate($start_date, $userTimezone)
{
    return $start_date = Carbon::parse($start_date, $userTimezone)
        ->startOfDay()
        ->setTimezone(config('app.timezone'));
}
function searchEndDate($end_date, $userTimezone)
{
    return  $end_date = Carbon::parse($end_date, $userTimezone)
        ->endOfDay()
        ->setTimezone(config('app.timezone'));
}

function timezoneCurrentDate($timezone)
{
    $tz_obj = new DateTimeZone($timezone);
    return new DateTime("now", $tz_obj);
}

function pagecount()
{
    return 50;
}

function esoId()
{
    return request()->eso_id;
}

function formatPhoneNumber($phoneNumber)
{
    $phoneNumber = preg_replace('/[^0-9]/', '', ltrim($phoneNumber, '0'));
    $phoneNumber = preg_replace('/[^0-9]/', '', ltrim($phoneNumber, '1'));

    if (strlen($phoneNumber) > 11) {
        $areaCode = substr($phoneNumber, 0, 4);
        $nextThree = substr($phoneNumber, 4, 4);
        $lastFour = substr($phoneNumber, 8, 4);

        $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
    } elseif (strlen($phoneNumber) > 10) {
        $countryCode = substr($phoneNumber, 0, strlen($phoneNumber) - 10);
        $areaCode = substr($phoneNumber, -11, 3);
        $nextThree = substr($phoneNumber, -8, 4);
        $lastFour = substr($phoneNumber, -4, 4);

        $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
    } elseif (strlen($phoneNumber) == 10) {
        $areaCode = substr($phoneNumber, 0, 3);
        $nextThree = substr($phoneNumber, 3, 3);
        $lastFour = substr($phoneNumber, 6, 4);

        $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
    } elseif (strlen($phoneNumber) == 7) {
        $nextThree = substr($phoneNumber, 0, 3);
        $lastFour = substr($phoneNumber, 3, 4);

        $phoneNumber = $nextThree . '-' . $lastFour;
    }

    return $phoneNumber;
}
function decimal2digitNumber($number, $precision = 2)
{
    $number = str_replace('$', '', $number);
    $number = round($number, 2);
    if (strpos($number, ".") !== false) {
        $number_arr = explode(".", $number);
        if (isset($number_arr[1]) && strlen($number_arr[1]) == 1) {
            $number = $number . '0';
        }
    } elseif ($number == '') {
        $number = '0.00';
    } else {
        $number = $number . '.00';
    }
    return $number;
}
function tripAddedBy($id)
{
    $type = ["", "Self Request", "Form Request", "Import", "GMR"];
    return $type[$id] ?? '';
}
function tripFormat($id)
{
    $type = ["", "Normal", "Return", "Will Call", "Wait-Time"];
    return $type[$id] ?? '';
}

function timestampDifference($start, $end)
{
    $start_date = strtotime($start);
    $end_date = strtotime($end);
    if ($start_date < $end_date) {
        $datediff = $end_date - $start_date;
        return round($datediff / (60 * 60 * 24));
    } else {
        $datediff = $start_date - $end_date;
        return -abs(round($datediff / (60 * 60 * 24)));
    }
}
function getGoogleKeyApi($franchise_id)
{
    $user = User::where('id', $franchise_id)->first();
    return $user->google_key ?? '';
}

function tripsCompleted($id)
{
    $user = TripMaster::where('driver_id', $id)->count();
    return $user;
}
function truncate_number($number, $precision = 2)
{
    //return number_format(floor($number *100)/100,2, '.', '');

    $number = round($number, $precision);
    if ($res = strpos($number, ".") !== false) {
        $number_arr = explode(".", $number);
        if (isset($number_arr[1]) && strlen($number_arr[1]) == 1) {
            $number = $number . '0';
        }
    } else {
        $number = $number . '.00';
    }
    return $number;

    //return bcdiv($number, 1, $precision);

    // Zero causes issues, and no need to truncate
    /*if ( 0 == (int)$number ) {
        return $number;
    }
    // Are we negative?
    $negative = $number / abs($number);
    // Cast the number to a positive to solve rounding
    $number = abs($number);
    // Calculate precision number for dividing / multiplying
    $precision = pow(10, $precision);
    // Run the math, re-applying the negative value to ensure returns correctly negative / positive
    return floor( $number * $precision ) / $precision * $negative;*/
}
function secondToTimes($waittime_sec)
{
    $hours = floor($waittime_sec / 3600);
    $minutes = floor(($waittime_sec / 60) % 60);
    $seconds = $waittime_sec % 60;

    $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
    $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
    $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

    return $hours . ':' . $minutes . ':' . $seconds;
}
function calculateWaitimeAmount($waittime_sec, $wait_time_per_hour)
{
    $wait_time_amount = 0;
    $per15minrate  = decimal2digitNumber($wait_time_per_hour / 4);
    $waititngtime_min = decimal2digitNumber($waittime_sec / 60);

    //less 15 min. get 15 amount
    if ($waititngtime_min > 5 && $waititngtime_min <= 15) {
        $wait_time_amount = $per15minrate;
    }

    //1 to 12 hrs for use this
    $range = array('1' => '4', '2' => '8', '3' => '12', '4' => '16', '5' => '20', '6' => '24', '7' => '28', '8' => '32', '9' => '36', '10' => '40', '11' => '44', '12' => '48');

    //more than 15. get multiple of 15 min amount
    if ($waititngtime_min > 15) {
        $numofwaiting = $waititngtime_min / 15;
        if (is_float($numofwaiting) && strpos($numofwaiting, ".") !== false) {
            $numofwaiting = intval($numofwaiting) + 1;
            $wait_time_amount = decimal2digitNumber($numofwaiting *  $per15minrate);
        } elseif (in_array($numofwaiting, $range)) {
            $multiple =  array_keys($range, $numofwaiting);
            $wait_time_amount = decimal2digitNumber($wait_time_per_hour * $multiple[0]);
        } else {
            $wait_time_amount = decimal2digitNumber($numofwaiting *  $per15minrate);
        }
    }

    if ($waititngtime_min > 45) {
        $wait_time_amount = $wait_time_per_hour;
    }

    return $wait_time_amount;
}
function payout($payout)
{
    if ($payout == 1) {
        $payout = "Mileage";
    } elseif ($payout == 2) {
        $payout = "Flat Rate (";
        $flat_rate = $trip["flat_rate"] ?? 0;
        $payout .= $flat_rate . '%)';
    } elseif ($payout == 3) {
        $payout = "Time";
    } elseif ($payout == 4) {
        $payout = "Base rate with time";
    } elseif ($payout == 5) {
        $payout = "No show (";
        $flat_rate = $trip["flat_rate"] ?? 0;
        $payout .= $flat_rate . '%)';
    } elseif ($payout == 6) {
        $payout = "Cancel (";
        $flat_rate = $trip["flat_rate"] ?? 0;
        $payout .= $flat_rate . '%)';
    } elseif ($payout == 7) {
        $payout = "Mileage+Time";
    } elseif ($payout == 8) {
        $payout = "Base Rate Per Mile Per Min";
    } else {
        $payout = "";
    }
    return $payout;
}
function providerInvoiceStatus($id)
{
    // provider status
    //  1-send,
    //  2-Rebill,
    //  3-Completed
    switch ($id) {
        case 1:
            return 'Unpaid'; //Billed
            break;
        case 2:
            return 'Partially Paid'; //     Rebill
            break;
        case 3:
            return 'Paid'; //Paid
            break;
        default:
            return 'NA';
    }
}

function minutesToSec($time)
{
    $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $time);
    sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
    return $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
}

function start_date($start_date)
{
    $start_date = Carbon::parse($start_date, eso()->timezone)
        ->startOfDay()
        ->setTimezone(config('app.timezone'))->format('Y-m-d');
    return  $start_date;
}

function end_date($end_date)
{
    $end_date = Carbon::parse($end_date, eso()->timezone)
        ->endOfDay()
        ->setTimezone(config('app.timezone'))->format('Y-m-d');
    return $end_date;
}

function awsasset($path, $minutes = 20)
{
    if ($path) {
        $path = removeFirstSlashIfAny($path);
        return Storage::temporaryUrl(
            $path,
            now()->addMinutes($minutes)
        );
    }
}

function removeFirstSlashIfAny($path)
{
    $check_for_slashe =  $path[0];
    if ($check_for_slashe == '/') {
        return  $path = substr($path, 1);
    }
    return $path;
}

function storeDateTime($date, $time = '', $timezone)
{
    $n = \Carbon\Carbon::parse($date . ' ' . $time, $timezone);
    return $n->setTimezone(config('app.timezone'));
}

// function modifyDriverLogTime($date, $time = '', $timezone = 'America/New_York')
// {
//     $n = \Carbon\Carbon::parse($date . ' ' . $time, $timezone);
//     $user = eso();
//     return $n->setTimezone($user->timezone);
// }

function saneDate($date)
{
    return   Carbon::createFromFormat('m-d-Y', $date)->format('Y-m-d');
}


function createTripNo($invoice_id)
{
    $len = strlen($invoice_id);
    $invoice_id = str_pad($invoice_id, 4, '0', STR_PAD_LEFT);
    return $invoice_id;
}

function createTicketNo($invoice_id)
{
    $len = strlen($invoice_id);
    $invoice_id = str_pad($invoice_id, 4, '0', STR_PAD_LEFT);
    return $invoice_id;
}

function todaysTrip()
{
    $user_timezone = eso();
    $tz = $user_timezone->timezone;
    $tz_obj = new DateTimeZone($tz);
    $today = new DateTime("now", $tz_obj);
    $today_date = $today->format('Y-m-d');
    // where('user_id', $user_id)
    $todaytrip = TripMaster::where('created_at', '>=', $today_date . " 00:00:00")
        ->where('created_at', '<=', $today_date . " 23:59:59")->where('leg_no', 1)->withTrashed()->get();
    if (count($todaytrip) > 0) {
        $count = count($todaytrip) + 1;
    } else {
        $count = 1;
    }

    $start_name = strtoupper(substr($user_timezone->name, 0, 3));
    $y = $today->format('y');
    $m = $today->format('m');
    $d = $today->format('d');

    return     $trip_custom_no = $start_name . $y . $m . $d . createTripNo($count);

    $todaytrip = TripMaster::where('trip_no', 'like', '%' . $start_name . $y . $m . $d . '%')->orderBy('trip_no', 'desc')->withTrashed()->first();
    if ($todaytrip) {
        $replace_id = str_replace($start_name . $y . $m . $d, '', $todaytrip->trip_no);
        $id_arr = explode('-', $replace_id);

        if (count($id_arr) > 1) {
            $count = (int)$id_arr[0] + 1;
            $trip_custom_no = $start_name . $y . $m . $d . createTripNo($count);
        }
    }

    return  $trip_custom_no;
}

function payorTypeModel($type)
{
    if ($type == 1) {
        return 'App\Models\Member';
    }
    if ($type == 2) {
        return 'App\Models\Crm';
    }

    if ($type == 3) {
        return 'App\Models\ProviderMaster';
    }
}

function tripGroupId()
{
    return uniqid();
}

function modifyTripTimeForEditTrip($date, $time, $timezone)
{
    $n = \Carbon\Carbon::parse($date . ' ' . $time, config('app.timezone'));
    return $n->setTimezone($timezone);
}

function nextTripNo($trip_no)
{
    //trip_no = APP2203010002-1
    $current_leg = substr($trip_no, -1); //output 1
    $new_leg = (int) $current_leg + 1;  // output 2
    $cutting_string = substr($trip_no, 0, -1); // output APP2203010002-
    return $cutting_string . $new_leg; // output APP2203010002-2
}

function weekDays($dates)
{
    $days = '';
    $weekMap = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    foreach ($dates as $key => $date) {
        $days .= $weekMap[$date] . ',';
    }

    return    rtrim($days, ',');
}


function examscore($driver_id)
{
    $total_records = DriverRequestFormFinalExamQuestionAnswer::where('driver_id', $driver_id)->count();
    $true_record = DriverRequestFormFinalExamQuestionAnswer::where('driver_id', $driver_id)->where('question_is_true', 1)->count();

    if ($total_records) {
        $percentage = decimal2digit_number($true_record * 100 / $total_records);
        $exam_is_done = 1;
    } else {
        $percentage = '';
        $exam_is_done = 0;
    }

    return array('exam_is_done' => $exam_is_done, 'percentage' => $percentage);
}

function decimal2digit_number($number, $precision = 2)
{
    $number = str_replace('$', '', $number);
    $number = round($number, 2);

    if (strpos($number, ".") !== false) {
        $number_arr = explode(".", $number);
        if (isset($number_arr[1]) && strlen($number_arr[1]) == 1) {
            $number = $number . '0';
        } elseif (isset($number_arr[1]) && strlen($number_arr[1]) > 2) {
            // Zero causes issues, and no need to truncate
            if (0 == (int)$number) {
                return $number;
            }
            // Are we negative?
            $negative = $number / abs($number);
            // Cast the number to a positive to solve rounding
            $number = abs($number);
            // Calculate precision number for dividing / multiplying
            $precision = pow(10, $precision);
            // Run the math, re-applying the negative value to ensure returns correctly negative / positive
            return $number = floor($number * $precision) / $precision * $negative;
        }
    } elseif ($number == '') {
        $number = '0.00';
    } else {
        $number = $number . '.00';
    }
    return $number;
}
function dateConvertToYMD($date, $format = 'm/d/Y')
{
    //  if (!validateDate($date, $format)) {
    //      return '0';
    //  }
    return Carbon::createfromformat($format, $date)->format('Y-m-d');
}

function splitName($full_name = '')
{
    $first_name  = '';
    $middle_name  = '';
    $last_name  = '';

    if ($full_name) {
        $full_name = preg_replace('/\s\s+/', ' ', $full_name);  // removing any extra spaces from string
        $name = explode(" ", $full_name); // converting string to array

        $first_name  = $name[0];
        $middle_name = implode(',', array_slice($name, 1, -1)); //removing first and last element from array
        if (count($name) > 1) {
            $last_name  = end($name);
        }
    }

    return [
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'last_name' => $last_name,
    ];
}

// remove all the special characters and alphabets
function getNumber($number)
{
    return        preg_replace("/[^0-9]+/", "", $number);
}

function concat($string1, $string2)
{
    return    $string1 . ' ' . $string2;
}

function concatAddress($pickup_address, $pickup_city, $pickup_state, $pickup_zip)
{
    return    $pickup_address . ' ' . $pickup_city . ' ' . $pickup_state . ' ' . $pickup_zip;
}

/*  function extractZipcode($address)
 {
     $zipcode = preg_match("/\b[A-Z]{2}\s+\d{5}(-\d{4})?\b/", $address, $matches);
     //print_r (explode(" ",$matches[0]));
     $arr = explode(" ", $matches[0] ?? '');
     return $arr[1] ?? '';
 } */

function extractZipcode($string)
{
    $zip = preg_match_all('/(?<!\d)\d{5,6}(?!\d)/', $string, $match) ? $match[0] : [];
    return $zip[0] ?? '';
}

function getGoogleKey()
{
    return     eso()->google_key;
}

/*
    var_dump(validateDate('2013-13-01'));  // false
    var_dump(validateDate('2012-02-29'));  // true
    var_dump(validateDate('2012', 'Y'));   // true
    var_dump(validateDate('12012', 'Y'));  // false
*/

function validateDate($date, $format = 'Y-m-d')
{
    $d = Carbon::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) == $date;
}


function getLatLng($address)
{
    $curl = curl_init();
    $apiKey =  getGoogleKey();
    //$address = "Sai Vittal Nagar, Bhosari Pimpri-Chinchwad, Maharashtra 411015";
    $formattedAddr = str_replace(' ', '+', $address);
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://maps.googleapis.com/maps/api/geocode/json?address=$formattedAddr&key=$apiKey",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $response = curl_exec($curl);
    //echo "<pre>".$response;
    curl_close($curl);
    $json = json_decode(trim($response), true);

    $latitude  = $json['results'][0]['geometry']['location']['lat'] ?? '';
    $longitude = $json['results'][0]['geometry']['location']['lng'] ?? '';

    return ['latitude' => $latitude, 'longitude' => $longitude];
}


function getGoogleDetails($origin, $destination)
{
    $apiKey = getGoogleKey(); //env('googleMapApiKey');
    $apiUrl = 'https://maps.googleapis.com/maps/api/directions/json';
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

function calculateTimes($appointment_or_pickup_time, $trip_dateTime, $trip_distance_in_seconds)
{
    if ($appointment_or_pickup_time) {
        return  $trip_dateTime->subSeconds($trip_distance_in_seconds)->format('H:i');
    }
    return null;
}

function uploadCsv($file, $path)
{
    $extension = $file->getClientOriginalExtension();
    $filename = uniqid() . '.' . $extension;
    return Storage::putFileAs($path, $file, $filename);
}



function sent_email_driver_step_eso_approval($data)
{
    $data_email = array();

    $is_approved = $data['is_approved']; //1 pending, 2 approved, 3 rejected

    if ($is_approved == 1) {
        $is_approved_status = 'Pending';
    }
    if ($is_approved == 2) {
        $is_approved_status = 'Approved';
    }
    if ($is_approved == 3) {
        $is_approved_status = 'Rejected';
    }

    $status = $data['current_step_status']; //0 pending, 1 approved, 2 rejected
    if ($status == 0) {
        $status_text = 'Pending';
    }
    if ($status == 1) {
        $status_text = 'Approved';
    }
    if ($status == 2) {
        $status_text = 'Rejected';
    }

    if ($data['inner_step_no'] == '2') {
        if ($status == 1) { //approved
            //driver_email
            $data_email[0]['bodytext'] = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
            
            <p>Your Step-2 application has been approved. Please login and continue the hiring process.</p>'; //
            $view = 'mails.admin.commonmailbody_job';
            $data_email[0]['subject'] = 'R4H - Step-2 Application Approved';
            $data_email[0]['to_mail'] = $data['driver_email'];
        }

        if ($status == 2) { //reject
            //driver_email
            $data_email[1]['bodytext'] = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
            <p>We are sorry to inform you that your application has been denied. If you  would like to know more about this, you may contact us at ' . $data['subdivision_email'] . '.</p>';
            //<p>The Step-'.$data['inner_step_no'].' of your job application has been rejected. Please contact '.ucfirst($data['subdivision_name']).' on '.$data['subdivision_email'].'</p>';
            $view = 'mails.admin.commonmailbody_job';
            $data_email[1]['subject'] = 'R4H - Step-2 Application Denied';
            $data_email[1]['to_mail'] = $data['driver_email'];
        }
    }


    if ($data['inner_step_no'] == '4') {
        if ($status == 1) { //approved
            //driver_email
            $data_email[0]['bodytext'] = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
            
            <p>Your Step-3 application has been approved. Please login and continue the hiring process.</p>'; //The Step-'.$data['inner_step_no'].' of your job application has been approved. Please login to continue the application process.</p>';
            $view = 'mails.admin.commonmailbody_job';
            $data_email[0]['subject'] = 'R4H - Step-3 Application Approved';
            $data_email[0]['to_mail'] = $data['driver_email'];
        }

        if ($status == 2) { //reject
            //driver_email
            $data_email[1]['bodytext'] = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
            <p>We are sorry to inform you that your application has been denied. If you  would like to know more about this, you may contact us at ' . $data['subdivision_email'] . '.</p>';
            //<p>The Step-'.$data['inner_step_no'].' of your job application has been rejected. Please contact '.ucfirst($data['subdivision_name']).' on '.$data['subdivision_email'].'</p>';
            $view = 'mails.admin.commonmailbody_job';
            $data_email[1]['subject'] = 'R4H - Step-3 Application Denied';
            $data_email[1]['to_mail'] = $data['driver_email'];
        }
    }


    if ($data['inner_step_no'] == '5_13') {
        if ($status == 1) { //approved
            //driver_email
            $data_email[0]['bodytext'] = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
            <p>Your training on the Step-4 application has been approved. Please login and continue the hiring process.</p>';
            $view = 'mails.admin.commonmailbody_job';
            $data_email[0]['subject'] = 'R4H - Step-4 Training Application Approved';
            $data_email[0]['to_mail'] = $data['driver_email'];
        }

        if ($status == 2) { //reject
            //driver_email
            $data_email[1]['bodytext'] = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
            <p>We are sorry to inform you that your application has been denied. If you  would like to know more about this, you may contact us at ' . $data['subdivision_email'] . '.</p>';
            $view = 'mails.admin.commonmailbody_job';
            $data_email[1]['subject'] = 'R4H - Step-4 Training Application Denied';
            $data_email[1]['to_mail'] = $data['driver_email'];
        }
    }

    if ($data['inner_step_no'] == '5') {
        if ($status == 1) { //approved
            //driver_email
            $data_email[0]['bodytext'] = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br>
            <p>Congratulations!</p><br>
            <p>Your application has been accepted. We will contact you soon.</p>';
            $view = 'mails.admin.commonmailbody_job';
            $data_email[0]['subject'] = 'R4H - Application Accepted';
            $data_email[0]['to_mail'] = $data['driver_email'];
        }

        if ($status == 2) { //reject
            //driver_email
            $data_email[1]['bodytext'] = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
            <p>We are sorry to inform you that your application has been denied. If you  would like to know more about this, you may contact us at ' . $data['subdivision_email'] . '.</p>';
            //<p>The Step-'.$data['inner_step_no'].' of your job application has been rejected. Please contact '.ucfirst($data['subdivision_name']).' on '.$data['subdivision_email'].'</p>';
            $view = 'mails.admin.commonmailbody_job';
            $data_email[1]['subject'] = 'R4H - Application Denied';
            $data_email[1]['to_mail'] = $data['driver_email'];
        }
    }

    if ($data['inner_step_no'] == '6') {
        if ($is_approved == 2) { //approved

            //driver_email
            $content = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
            <p>Congratulations!</p></br></br>
            <p>Your application has been approved. We will contact you soon. Thank You';
            $data_email[0]['bodytext'] = $content;
            $view = 'mails.admin.commonmailbody_job';
            $data_email[0]['subject'] = 'Application Approved';
            $data_email[0]['to_mail'] = $data['driver_email'];
        }

        if ($status == 2) { //reject
            //driver_email
            $data_email[1]['bodytext'] = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
            <p>We are sorry to inform you that your application has been denied. If you  would like to know more about this, you may contact us at ' . $data['subdivision_email'] . '.</p>';
            $view = 'mails.admin.commonmailbody_job';
            $data_email[1]['subject'] = 'Application Denied';
            $data_email[1]['to_mail'] = $data['driver_email'];
        }
    }

    foreach ($data_email as $e) {
        try {
            $emailData = array();
            $emailData['bodytext'] = $e['bodytext'];
            $view = 'mails.admin.commonmailbody_job';
            $subject = $e['subject'];
            Mail::to($e['to_mail'])->send(new CommonMail('', $view, $subject, $emailData));
        } catch (\Exception $e) {
            //echo $e->getMessage();
            //exit;
        }
    }

    return true;
}

function sent_email_driver_step_message($data)
{
    $data_email = array();

    if ($data['inner_step_no'] == '2') {
        //driver_email
        $bodytext = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
        <p>You are requested to provide the following information for the Step-2 driver application.</p>';
        $bodytext .= $data['status_remark'];
        $data_email[0]['bodytext'] = $bodytext;

        $view = 'mails.admin.commonmailbody_job';
        $data_email[0]['subject'] = 'Feedback on Step-2 Driver application';
        $data_email[0]['to_mail'] = $data['driver_email'];
    }

    if ($data['inner_step_no'] == '4') {
        //driver_email
        $bodytext = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
        <p>You are requested to provide the following information for the Step-3 driver application.</p>';
        $bodytext .= $data['status_remark'];
        $data_email[0]['bodytext'] = $bodytext;

        $view = 'mails.admin.commonmailbody_job';
        $data_email[0]['subject'] = 'Feedback on Step-3 Driver application';
        $data_email[0]['to_mail'] = $data['driver_email'];
    }

    if ($data['inner_step_no'] == '5_13') {
        //driver_email
        $bodytext = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
        <p>You are requested to provide the following information for the training on the Step-4 driver application.</p>';
        $bodytext .= $data['status_remark'];
        $data_email[0]['bodytext'] = $bodytext;

        $view = 'mails.admin.commonmailbody_job';
        $data_email[0]['subject'] = 'Feedback on Step-4 Training Driver application';
        $data_email[0]['to_mail'] = $data['driver_email'];
    }

    if ($data['inner_step_no'] == '5') {
        //driver_email
        $bodytext = '<p>Dear ' . ucfirst($data['driver_name']) . ',</p></br></br>
        <p>You are requested to provide the following information for the Step-4 driver application.</p>';
        $bodytext .= $data['status_remark'];
        $data_email[0]['bodytext'] = $bodytext;

        $view = 'mails.admin.commonmailbody_job';
        $data_email[0]['subject'] = 'Feedback on Step-4 Driver application';
        $data_email[0]['to_mail'] = $data['driver_email'];
    }

    foreach ($data_email as $e) {
        try {
            $emailData = array();
            $emailData['bodytext'] = $e['bodytext'];
            $view = 'mails.admin.commonmailbody_job';
            $subject = $e['subject'];
            Mail::to($e['to_mail'])->send(new CommonMail('', $view, $subject, $emailData));
        } catch (\Exception $e) {
            //echo $e->getMessage();
            //exit;
        }
    }

    return true;
}

function googleMatrix($start_address, $end_address)
{
    $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
        'origins' => $start_address,
        'destinations' =>$end_address,
        'key' =>env('googleMapApiKey')
    ]);
    $result = json_decode($response, true);
    $distance_in_miles = 0 ;
    $time_in_seconds = 0 ;
    if (isset($result['rows'][0]['elements'][0]['distance'])) {
        $distance_in_miles = (float) ($result['rows'][0]['elements'][0]['distance']['text']) * 0.621371;
 
        $time_in_seconds = $result['rows'][0]['elements'][0]['duration']['value'];
    }

    return ['distance_in_miles' =>round($distance_in_miles, 2) , 'time_in_seconds'=>$time_in_seconds ] ;
}

function tripSearchArray()
{
    return  $searchBy = [
        "trip_no",
        "date_of_service",
        "appointment_time",
        "shedule_pickup_time",
        "member_name",
        "member_phone_no",
        "pickup_address",
        "drop_address",
        "notes_or_instruction",
        "level_of_service",
        'additional_passengers',
        'estimated_trip_distance',
        'estimated_mileage_frombase_location',
        'trip_price',
        'adjusted_price',
        'trip_start_address',
        'total_price'
    ];
}
function getTodayDate()
{
    $timezone = eso()->timezone;
    $tz_obj = new DateTimeZone($timezone);
    $today = new DateTime("now", $tz_obj);
    return  $today->format('Y-m-d');
}
