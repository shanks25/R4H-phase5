<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Driverclockout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:driverclockout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

                if ($currentime_hr == '0') {
                    $drivers = DriverMaster::where('user_id', $r->id)->get();

                    foreach($drivers as $d)
                    {
                        $getlastin = DriverUtilizationDetail::where('driver_id', $d->id)->whereRaw('(out_time = "" or out_time is NULL)')->orderby('id', 'desc')->first();
                        
                        if($getlastin)
                        {
                            $created_at = $getlastin->created_at;
                            $lastlog = TripStatusLog::select('*')->where('driver_id', $d->id)->where('created_at', '>', $created_at)->orderby('id', 'desc')->first();

                            if($lastlog)
                            {
                                DriverUtilizationDetail::where('id', $getlastin->id)->update(array('out_time' => date('H:i:s', strtotime($lastlog->date_time))));
                            }
                            else
                            {
                                DriverUtilizationDetail::where('id', $lastlog->id)->update(array('out_time', '23:59:59'));
                            }
                        }

                        $postArray = ['access_token' => "", 'device_token' => ''];
                        DriverMaster::where('id', $d->id)->update($postArray);
                    }
                }
            }
        }
    }
}
