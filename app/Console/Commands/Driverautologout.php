<?php

namespace App\Console\Commands;

use App\Mail\CommonMail;
use App\Model\TripMaster;
use App\Model\Drivernotifications;
use App\Model\Usernotifications;
use App\Model\DriverMaster;
use App\Model\DriverUtilizationDetail;
use App\Model\TripStatusLog;
use App\Model\TempTable;
use App\Model\User;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class Driverautologout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:driverautologout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description automatically logout driver using this cron command every day';

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
        $sub_division = User::get();
        if (count($sub_division) > 0) {
            foreach ($sub_division as $r) {
                $currentime =  modifyTripTime(date('Y-m-d'), date("g:i A", strtotime(date('H:i:s'))), $r->timezone)->toTimeString();
                $currentime_hr =  date('H', strtotime($currentime));

                if ($currentime_hr == '4') {
                    $drivers = DriverMaster::where('user_id', $r->id)->whereRaw('(access_token is not null or access_token != "")')->get();

                    foreach($drivers as $d)
                    {
                        $total_trip = TripMaster::select(DB::raw('COUNT(id) as total_count'))->whereIn('status_id', [4,10,11,12])->where('Driver_id', $d->id)->first();

                        if ($total_trip->total_count <= 0) {
                            /*$dr_utl = DriverUtilizationDetail::where('driver_id', $d->id)->orderby('id', 'desc')->first();
                            if($dr_utl && $dr_utl->in_out == 'in')
                            {
                                $utilization_date = $dr_utl->utilization_date;
                                $lastlog = TripStatusLog::select('*')->where('driver_id', $d->id)->where('date_time', '>', $utilization_date)->orderby('id', 'desc')->first();

                                if($lastlog)
                                {
                                    $insertArr = array(
                                        "driver_id" => $d->id,
                                        "desk_time" => $lastlog->date_time,
                                        "in_out"   =>  'out',
                                        "utilization_date" => $utilization_date,
                                        "timezone_name" => $lastlog->timezone_name,
                                        "timezone"   =>  $lastlog->timezone
                                    );
                                    DriverUtilizationDetail::insert($insertArr);
                                }
                                else
                                {
                                    $insertArr = array(
                                        "driver_id" => $d->id,
                                        "desk_time" => $dr_utl->desk_time,
                                        "in_out"   =>  'out',
                                        "utilization_date" => $utilization_date,
                                        "timezone_name" => $dr_utl->timezone_name,
                                        "timezone"   =>  $dr_utl->timezone
                                    );
                                    DriverUtilizationDetail::insert($insertArr);
                                }
                            }*/

                            $postArray = ['access_token' => "", 'device_token' => ''];
                            DriverMaster::where('id', $d->id)->update($postArray);
                        }
                    }
                }
            }
        }
    }
}
