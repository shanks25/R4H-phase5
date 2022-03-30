<?php

namespace App\Console\Commands;

use App\Mail\CommonMail;
use App\Model\TripMaster;
use App\Model\Drivernotifications;
use App\Model\Usernotifications;
use App\Model\DriverMaster;
use App\Model\TempTable;
use App\Model\User;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class GooglekeyNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:googlekeyexpiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description automatically logout driver using this cron command every day check command to google api key is workinh for franchise if not then logout franchise and driver in app';

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
        $sub_division = User::where('google_key_valid', 1)->where('status', '1')->groupBy('google_key')->get();
        if (count($sub_division) > 0) {
            foreach ($sub_division as $r) {
                $res = checkgooglemapkeyvalid(trim($r->google_key));
                if ($res == 'Invalid') {
                    $data = array('google_key_valid' => 0);
                    User::where('google_key', trim($r->google_key))->update($data);
                    ////ALL DRIVER SHOULD BE LOGOUT 
                    // $login_id = $r->id;
                    // $postArray = ['access_token' => "", 'device_token' => ''];
                    // DriverMaster::where(['user_id' => $login_id])->update($postArray);
                    ///////////////
                }
            }
        }
        // temp table set count and updated time for track last executed cron 
        // $temp_tbl = TempTable::where('id', 1)->first(); // id 1 for google key expiration 
        // $temp_tbl->count = $temp_tbl->count + 1;
        // $temp_tbl->save();
        // echo "success";
    }
}
