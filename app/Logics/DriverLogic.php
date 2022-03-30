<?php

namespace App\Logics;

use App\Models\DriverLeaveDetail;

class DriverLogic
{
    public static function LeaveDriverIds($date)
    {
        $leave_data = DriverLeaveDetail::select('driver_id')
            ->whereRaw("'$date' BETWEEN start_date AND end_date")
            ->where('status', '1')
            ->get()
            ->toArray();
        $driver_leave_ids = array();
        if (count($leave_data) > 0) {
            $driver_leave_ids = array_column($leave_data, 'driver_id');
        }
        return $driver_leave_ids;
    }
}
