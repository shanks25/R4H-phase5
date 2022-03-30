<?php

namespace App\Console\Commands;

use App\Mail\CommonMail;
use App\Model\TripMaster;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class Driverpay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:makeDriverpay';

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
        $status_arr = array('3', '5', '6');
        // echo "hello0";
        // exit;
        $trip_data = TripMaster::select('id', 'date_of_service', 'confirm_status', 'status_id', 'Driver_id')->whereIn('status_id', $status_arr)->get()->toArray();
        // echo count($trip_data);
        // exit;
        $i = 0;
        if (!empty($trip_data)) {
            foreach ($trip_data as $row) {
                $row = (array)$row;

                $Driver_id = $row['Driver_id'];
                $trip_id = $row['id'];

                $res = calculate_driver_pay($Driver_id, $trip_id);
                $total_pay_to_driver = $res['total_pay_to_driver'];

                $update_arr = array(
                    'driver_pay' => $total_pay_to_driver
                );
                TripMaster::where('id', $trip_id)->update($update_arr);
                // echo "success <br />";
                // echo $i++;
                // echo "1 is complete ";
                // exit;
            }
        }

        echo "success";
    }
}
