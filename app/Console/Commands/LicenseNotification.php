<?php

namespace App\Console\Commands;

use App\Mail\CommonMail;
use App\Model\TripMaster;
use App\Model\Drivernotifications;
use App\Model\DriverMaster;

use App\Model\VehicleMaster;
use App\Model\Usernotifications;
use App\Model\Driverleavedetails;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use DateInterval;
use DatePeriod;

class LicenseNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:licenseexpiry';

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
        $date = date('Y-m-d');

        //driver leave       
        $previous_date = date('Y-m-d', mktime(0, 0, 0, date("m", strtotime($date)), date("d", strtotime($date))-1, date("Y", strtotime($date))));

        $end_leave = Driverleavedetails::select('*')->where(DB::raw("(DATE_FORMAT(end_date,'%Y-%m-%d'))"), '<=', $previous_date)->where('status', '1')->get();

        if (!empty($end_leave)) {
            foreach ($end_leave as $l) {
                $update = array("status" => 2, "updated_at" => date('Y-m-d H:i:s'));
                Driverleavedetails::where('id', $l->id)->update($update);

                $license_expired_flag = '0';
                $driver_data = DriverMaster::select('id', 'license_expiry', 'name', 'user_id')->where('license_expiry', '!=', '')->where('id', $l->driver_id)->first();
                
                if ($driver_data) {
                    if($driver_data->license_expiry != '' && $driver_data->license_expiry != NULL)
                    {
                        $license_days = timestamp_difference(date('Y-m-d'), $driver_data->license_expiry);
                        if($license_days < 0)
                        {
                            $license_expired_flag = '1';
                        }
                    }
                }

                if($license_expired_flag == '0')
                {
                    $update = array("status" => '1', "on_leave" => 0, "updated_at" => date('Y-m-d H:i:s'));
                    DriverMaster::where('id', $l->driver_id)->where('status', '0')->update($update);
                }
                else
                {
                    $update = array("on_leave" => 0, "updated_at" => date('Y-m-d H:i:s'));
                    DriverMaster::where('id', $l->driver_id)->update($update);
                }
            }
        }


        $leave_data = Driverleavedetails::whereRaw("'$date' BETWEEN start_date AND end_date")->where('status', '1')->get();
        
        if (!empty($leave_data)) {
            foreach ($leave_data as $l) {
                
                $start_date = date('Y-m-d', strtotime($l->start_date));
                $end_date = date('Y-m-d', strtotime($l->end_date));
                $trips = TripMaster::whereBetween("date_of_service", [$start_date, $end_date])->where('Driver_id', $l->driver_id)->get();

                foreach($trips as $t)
                {
                    $update = array("Driver_id" => '0', "status_id" => 2, "current_status" => 0, "updated_at" => date('Y-m-d H:i:s'));
                    TripMaster::where('id', $t->id)->update($update);
                }

                $update = array("status" => '0', "on_leave" => 1, "updated_at" => date('Y-m-d H:i:s'));
                DriverMaster::where('id', $l->driver_id)->where('status', '1')->update($update);
            }
        }
        //end leave



        $driver_data = DriverMaster::select('id', 'name', 'email', 'device_type', 'device_token', 'access_token', 'license_no', 'license_expiry', 'user_id')->where('license_expiry', '>=', $date)->where('status', '1')->get();
        
        if (!empty($driver_data)) {
            foreach ($driver_data as $r) {
                $days = $this->timestamp_difference(date('Y-m-d'), $r->license_expiry);

                if($days == '0')
                {
                    $insert_arr = array("driver_id" => $r->id, "post_by" => $r->user_id, "notification" => 'Reminder: Your Driver License is expiring today.', "is_read" => 0, "created_at" => date('Y-m-d H:i:s'), "link" => 'Profile');
                    Drivernotifications::insert($insert_arr);
                }
                else if($days == '1')
                {
                    $insert_arr = array("driver_id" => $r->id, "post_by" => $r->user_id, "notification" => 'Reminder: Your Driver License is expiring tomorrow.', "is_read" => 0, "created_at" => date('Y-m-d H:i:s'), "link" => 'Profile');
                    Drivernotifications::insert($insert_arr);
                }
                else if($days == '7' || $days == '14' || $days == '21')
                {
                    $insert_arr = array("driver_id" => $r->id, "post_by" => $r->user_id, "notification" => 'Reminder: Your Driver License is expiring soon.', "is_read" => 0, "created_at" => date('Y-m-d H:i:s'), "link" => 'Profile');
                    Drivernotifications::insert($insert_arr);
                }
                else if($days == '29')
                {
                    $insert_arr = array("driver_id" => $r->id, "post_by" => $r->user_id, "notification" => 'Reminder: Your Driver License is expiring a month.', "is_read" => 0, "created_at" => date('Y-m-d H:i:s'), "link" => 'Profile');
                    Drivernotifications::insert($insert_arr);
                }
            }
        }

        $date = date('Y-m-d');
        $driver_data = DriverMaster::select('id', 'license_expiry', 'name', 'user_id')->where('license_expiry', '!=', '')->where('status', '1')->get();
        
        if (!empty($driver_data)) {
            foreach ($driver_data as $r) {
                if($r->license_expiry != '' && $r->license_expiry != NULL)
                {
                    $license_days = $this->timestamp_difference(date('Y-m-d'), $r->license_expiry);
                    
                    if($license_days < 0 || $license_days < 0)
                    {
                        $update = array("status" => 0, "updated_at" => date('Y-m-d H:i:s'));
                        DriverMaster::where('id', $r->id)->update($update);

                        //sub-division push notification 
                        $insert_arr = array("user_id" => $r->user_id, "post_by" => 1, "notification" => 'Driver '.$r->name.' account is deactivated due to license is expired.', "created_date" => date('Y-m-d H:i:s'));
                        Usernotifications::insert($insert_arr);
                    }
                }
            }
        }


        


        $date = date('Y-m-d');
        $vehicle_data = VehicleMaster::select('id', 'VIN', 'registration_expiry_date', 'insurance_expiry_date', 'user_id')->whereRaw('registration_expiry_date >='.$date.' or insurance_expiry_date >='.$date)->where('status', '1')->get();
        
        if (!empty($vehicle_data)) {
            foreach ($vehicle_data as $r) {
                $reg_days = $this->timestamp_difference(date('Y-m-d'), $r->registration_expiry_date);
                $ins_days = $this->timestamp_difference(date('Y-m-d'), $r->insurance_expiry_date);

                if($reg_days == '0' || $ins_days == '0')
                {
                    $msg = '';
                    if($reg_days == '0' && $ins_days == '0')
                    {
                        $msg .= 'Reminder: Your Vehicle registration and insurance is expiring today. ';
                    }
                    else if($reg_days == '0')
                    {
                        $msg .= 'Reminder: Your Vehicle registration is expiring today.';
                    }
                    else if($ins_days == '0')
                    {
                        $msg .= 'Reminder: Your Vehicle insurance is expiring today.';
                    }

                    $insert_arr = array("driver_id" => $r->id, "post_by" => $r->user_id, "notification" => $msg, "is_read" => 0, "created_at" => date('Y-m-d H:i:s'), "link" => 'Profile');
                    Drivernotifications::insert($insert_arr);


                    $msg = '';
                    if($reg_days == '0' && $ins_days == '0')
                    {
                        $msg .= 'Reminder: Vehicle ('.$r->VIN.') registration and insurance is expiring today. ';
                    }
                    else if($reg_days == '0')
                    {
                        $msg .= 'Reminder: Vehicle ('.$r->VIN.') registration is expiring today. ';
                    }
                    else if($ins_days == '0')
                    {
                        $msg .= 'Reminder: Vehicle ('.$r->VIN.') insurance is expiring today.';
                    }

                    //sub-division push notification 
                    $insert_arr = array("user_id" => $r->user_id, "post_by" => 1, "notification" => $msg, "created_date" => date('Y-m-d H:i:s'));
                    Usernotifications::insert($insert_arr);
                }

                if($reg_days == '29' || $ins_days == '29')
                {
                    $msg = '';
                    if($reg_days == '29' && $ins_days == '29')
                    {
                        $msg .= 'Reminder: Your Vehicle registration and insurance is expiring soon. ';
                    }
                    else if($reg_days == '29')
                    {
                        $msg .= 'Reminder: Your Vehicle registration is expiring soon.';
                    }
                    else if($ins_days == '29')
                    {
                        $msg .= 'Reminder: Your Vehicle insurance is expiring soon.';
                    }

                    $insert_arr = array("driver_id" => $r->id, "post_by" => $r->user_id, "notification" => $msg, "is_read" => 0, "created_at" => date('Y-m-d H:i:s'), "link" => 'Profile');
                    Drivernotifications::insert($insert_arr);


                    $msg = '';
                    if($reg_days == '29' && $ins_days == '29')
                    {
                        $msg .= 'Reminder: Vehicle ('.$r->VIN.') registration and insurance is expiring soon. ';
                    }
                    else if($reg_days == '29')
                    {
                        $msg .= 'Reminder: Vehicle ('.$r->VIN.') registration is expiring soon. ';
                    }
                    else if($ins_days == '29')
                    {
                        $msg .= 'Reminder: Vehicle ('.$r->VIN.') insurance is expiring soon.';
                    }

                    //sub-division push notification 
                    $insert_arr = array("user_id" => $r->user_id, "post_by" => 1, "notification" => $msg, "created_date" => date('Y-m-d H:i:s'));
                    Usernotifications::insert($insert_arr);
                }
            }
        }


        $date = date('Y-m-d');
        $vehicle_data = VehicleMaster::select('id', 'VIN', 'registration_expiry_date', 'insurance_expiry_date', 'user_id')->whereRaw("(registration_expiry_date != NULL or  insurance_expiry_date != NULL)")->where('status', '1')->get(); 
        
        if (!empty($vehicle_data)) {
            foreach ($vehicle_data as $r) {
                $vehicle_id = $r->id;
                $user_id = $r->user_id;


                if($r->insurance_expiry_date != '' && $r->registration_expiry_date != '')
                {
                    $reg_days = $this->timestamp_difference(date('Y-m-d'), $r->registration_expiry_date);
                    $ins_days = $this->timestamp_difference(date('Y-m-d'), $r->insurance_expiry_date);

                    if($reg_days < 0 || $ins_days < 0)
                    {
                        $update = array("status" => 0, "updated_at" => date('Y-m-d H:i:s'));
                        VehicleMaster::where('id', $vehicle_id)->update($update);

                        $driver = DriverMaster::where('vehicle_id', $vehicle_id)->first();
                        if($driver)
                        {
                            $driver_id = $driver->id;
                            $driver_name = $driver->name;
                            
                            $insert_arr = array("driver_id" => $driver_id, "post_by" => $user_id, "notification" => 'Your account and Vehicle '.$r->VIN.' is deactivated due to vehicle registration / insurance date is expired.', "is_read" => 0, "created_at" => date('Y-m-d H:i:s'), "link" => 'Profile');
                            Drivernotifications::insert($insert_arr);

                            //sub-division push notification 
                            $insert_arr = array("user_id" => $user_id, "post_by" => 1, "notification" => 'Driver '.$driver_name.' and Vehicle '.$r->VIN.' both are deactivated due to vehicle registration / insurance date is expired.', "created_date" => date('Y-m-d H:i:s'));
                            Usernotifications::insert($insert_arr);

                            $update = array("status" => 0, "updated_at" => date('Y-m-d H:i:s'));
                            DriverMaster::where('id', $driver_id)->update($update);
                        }
                        else
                        {
                            //sub-division push notification 
                            $insert_arr = array("user_id" => $user_id, "post_by" => 1, "notification" => 'Vehicle '.$r->VIN.' is deactivated due to vehicle registration / insurance is expired.', "created_date" => date('Y-m-d H:i:s'));
                            Usernotifications::insert($insert_arr);
                        }
                    }
                }
            }
        }

        return "success";
    }


    public static function timestamp_difference($start, $end)
    {
        $start = strtotime($start); // or your date as well
        $your_date = strtotime($end);

        if($start < $your_date)
        {
            $datediff = $your_date - $start;
            return round($datediff / (60 * 60 * 24));
        }
        else
        {
            $datediff = $start - $your_date;
            return -abs(round($datediff / (60 * 60 * 24)));
        }
    }
}
