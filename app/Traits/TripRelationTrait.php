<?php

namespace App\Traits;

use App\Models\Member;
use App\Models\TripLog;
use App\Models\Vehicle;
use App\Models\ZoneZip;
use App\Models\PayorType;
use App\Models\TripImport;
use App\Models\TripMaster;
use App\Models\BaseLocation;
use App\Models\CountyMaster;
use App\Models\DriverMaster;
use App\Models\StatusMaster;
use App\Models\MemberAddress;
use App\Models\TripStatusLog;
use App\Models\MasterLevelOfService;

trait TripRelationTrait
{
    public function driver()
    {
        return $this->belongsTo(DriverMaster::class, 'driver_id');
    }
    public function status()
    {
        return $this->belongsTo(StatusMaster::class, 'status_id');
    }

    public function payor()
    {
        return $this->morphTo(__FUNCTION__, 'payable_type', 'payor_id');
    }

    public function payorTypeNames()
    {
        return $this->belongsTo(PayorType::class, 'payor_type');
    }

    public function levelOfService()
    {
        return $this->belongsTo(MasterLevelOfService::class, 'master_level_of_service_id');
    }
    public function baselocation()
    {
        return $this->belongsTo(BaseLocation::class, 'base_location_id');
    }
    public function zone()
    {
        return $this->hasMany(ZoneZip::class, 'zipcode', 'pickup_zip');
    }
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
    public function importFile()
    {
        return $this->belongsTo(TripImport::class, 'importfile_id');
    }

    public function log()
    {
        return $this->hasOne(TripLog::class, ['trip_id', 'driver_id'], ['id', 'driver_id']);
    }
    public function statusLogs()
    {
        return $this->hasMany(TripStatusLog::class, ['trip_id', 'driver_id'], ['id', 'driver_id']);
    }
    public function statusTimeLogs()
    {
        return $this->hasMany(TripStatusLog::class, ['trip_id', 'driver_id'], ['id', 'driver_id']);
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function pickupDetails()
    {
        return $this->belongsTo(MemberAddress::class, 'pickup_member_address_id');
    }

    public function dropDetails()
    {
        return $this->belongsTo(MemberAddress::class, 'drop_member_address_id');
    }
    public function statusPickupTime()
    {
        return $this->hasOne(TripStatusLog::class, ['trip_id', 'driver_id'], ['id', 'Driver_id']);
    }
    public function statusDropTime()
    {
        return $this->hasOne(TripStatusLog::class, ['trip_id', 'driver_id'], ['id', 'Driver_id']);
    }
    public function countyPickupNames()
    {
        return $this->hasOne(CountyMaster::class, 'zip', 'pickup_zip');
    }
    public function countyDropNames()
    {
        return $this->belongsTo(CountyMaster::class, 'dropoff_zip', 'zip');
    }

    public function downLegs()
    {
        return $this->hasMany(TripMaster::class, 'parent_id');
    }
}
