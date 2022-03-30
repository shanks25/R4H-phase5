<?php

namespace App\Console\Commands;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Mail\CommonMail;
use App\Model\TripMaster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ExpireTrip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:makeExpireTrip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update trp status expire if time is old';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mytimezone = date_default_timezone_get();

        $curr_time_ct = new DateTime(date("Y-m-d H:i:s"), new DateTimeZone($mytimezone));
        $currdatetime_server    = $curr_time_ct->format('Y-m-d H:i:s');
        $curr_time_ct->setTimeZone(new DateTimeZone('UTC'));
        $currdatetime    = $curr_time_ct->format('Y-m-d H:i:s');

        $update_arr = [];
        $arr_status = array(3, 5, 6, 8, 9, 13);
        $html = '';

        $trip_data = TripMaster::select('id', 'date_of_service', 'appointment_time', 'confirm_status', 'status_id', DB::raw("CONCAT(date_of_service,  ' ', appointment_time) as trip_date"), 'timezone', 'short_timezone', 'notes_or_instruction', 'Driver_id', 'shedule_pickup_time')->whereNotIn('status_id', $arr_status)->get()->toArray();

        if (!empty($trip_data)) {
            foreach ($trip_data as $row123) {
                $row = (array)$row123;
                $trip_timezone = $row['timezone'];
                $short_timezone = $row['short_timezone'];
                $notes_or_instruction = trim($row['notes_or_instruction']);
                $trip_date = $row['date_of_service'];

                $newtimezone = getTimeZoneName($short_timezone);
                if ($row['shedule_pickup_time'] == "00:00:00" || $row['shedule_pickup_time'] == null || $row['shedule_pickup_time'] == '') {
                    $shedule_pickup_time =  ConvertDateTimeFromPST($row['date_of_service'], '', $newtimezone)->toDateString();
                } else {
                    try {
                        $shedule_pickup_time =  ConvertDateTimeFromPST($row['date_of_service'], date("g:i A", strtotime($row['shedule_pickup_time'])), $newtimezone)->toDateString();
                    } catch (\Exception $e) {
                        $shedule_pickup_time =  $row['date_of_service'];
                    }
                }

                //echo "<br/> Trip Date :".$trip_timezone;
                //$newtimezone = timezone_name_from_abbr($short_timezone);

                /*if ($short_timezone == "EST") {
                    $newtimezone = "America/New_York";
                } else if ($short_timezone == "PST") {
                    $newtimezone = "America/Los_Angeles";
                } else if ($short_timezone == "CST") {
                    $newtimezone = "America/Chicago";
                } else if ($short_timezone == "AST") {
                    $newtimezone = "Asia/Riyadh";
                } else if ($short_timezone == "UTC+10") {
                    $newtimezone = "Pacific/Guam";
                }*/
               
                if ($short_timezone != "") {
                    $date = new DateTime("now", new DateTimeZone($newtimezone));
                    $currdatetime = date('Y-m-d', strtotime(Carbon::now()));
                }
                else
                {
                    $n = \Carbon\Carbon::parse(date('Y-m-d H:i:s'), config('app.timezone'));
                    $currdatetime = date('Y-m-d', strtotime($n->setTimezone($newtimezone)));
                }
    
                $html .= "<br/> Trip ID :" . $row['id'] . " Trip Date :" . $trip_date . ' = Currenttime:' . $currdatetime . '<br>';

                if ($shedule_pickup_time < $currdatetime) {
                    if ($row['status_id'] == 2 || $row['status_id'] == 1 || $row['status_id'] == 7) {
                        $update_arr = array(
                            'status_id' => 8,
                            'payout_type' => '0',
                            'log_status' => 'NA'
                        );
                    } elseif ($row['status_id'] == 4) {
                        $update_arr = array(
                            'status_id' => 9,
                            'confirm_status' => '2',
                            'log_status' => 'NA'
                        );
                    } elseif ($row['status_id'] == 10 || $row['status_id'] == 11 || $row['status_id'] == 12) {
                        $notes_or_instruction = $notes_or_instruction . '(Auto completed)';
                        $update_arr = array(
                            'status_id' => 3,
                            'confirm_status' => '9',
                            'notes_or_instruction' => trim($notes_or_instruction),
                            'log_status' => 'Missing'
                        );
                    } elseif ($row['status_id'] == 6 || $row['status_id'] == 8) {
                        $update_arr = array(
                            'payout_type' => '0'
                        );
                    } else {
                        $update_arr = array(
                            'status_id' => $row['status_id']
                        );
                    }
                    TripMaster::where('id', $row['id'])->update($update_arr);

                    if ($row['status_id'] == 10 || $row['status_id'] == 11 || $row['status_id'] == 12) {
                        updatetripsprofit($row['id']);
                    }
                }
            }
        }

        /*try {
            $data['bodytext'] = '<p>Dear Sumit,</p></br></br>
            currdatetime_server:' . $currdatetime_server . ' currdatetime UTC:' . $currdatetime . ' Html:=' . $html;
            $view = 'mails.admin.commonmailbody';
            $subject = url('/') . 'Ride4Health cron run';
            Mail::to('sumit@apptechinnovations.com')->send(new CommonMail('', $view, $subject, $data));
        } catch (\Exception $e) {
            //echo $e->getMessage();
        }*/

        echo $html;
    }
}
