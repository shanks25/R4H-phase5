<?php

namespace App\Models;

use App\Traits\LocalScopes;
use Facades\App\Repository\TripRepo;
use App\Traits\TripRelationTrait;
use App\Traits\TripTrait;
use App\Models\RelInvoiceItem;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\TripPayoutDetail;

class TripMaster extends Model
{
    use \Awobaz\Compoships\Compoships;
    use HasFactory, LocalScopes, SoftDeletes, TripRelationTrait, TripTrait;
    protected $table = 'trip_master_ut';
    protected $guarded = ['legs', 'eso_id'];


    public function scopeNextLeg($query, $trip)
    {
        return $query->where([['leg_no', '>', $trip->leg_no], ['group_id', '=', $trip->group_id]]);
    }

    public static function nonOperationalTrips()
    {
        $statues = ['1' => 'Assigned', '2' => 'Unassigned'];
        return array_keys($statues);
    }


    public function scopeMassAssignColumns($query, $value = [])
    {
        return $query->select(array_diff(TripRepo::massAssignColumns(), (array) $value));
    }


    // public function getDateOfServiceAttribute($value)
    // {
    //     return modifyTripDate($value, $this->shedule_pickup_time);
    // }
    // public function getShedulePickupTimeAttribute($value)
    // {
    //     return modifyTripTime($this->date_of_service, $value);
    // }

    public function statuslog()
    {
        return $this->hasMany(TripStatusLog::class, 'trip_id', 'id', 'driver_id', 'driver_id');
    }
    public function getPayorPDF($id_arr)
    {
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no,VIN',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'log',
            'statuslog:id,driver_id,trip_id,status,timezone,date_time',
            'member:id,name',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        $payorlog = $this->trips($request, $with_array)->whereIn('id', $id_arr)->get();
        return $payorlog;
    }

    public function franchise()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function tripPayoutDetail()
    {
        return $this->hasMany(TripPayoutDetail::class, 'trip_id');
    }
    public function driverServiceRate()
    {
        return $this->hasMany(DriverServiceRate::class, 'driver_id');
    }
    public function driver()
    {
        return $this->belongsTo('App\Models\DriverMaster', 'Driver_id');
    }

    public function relInvoiceItem()
    {
        return $this->hasOne(RelInvoiceItem::class, 'trip_id', 'id');
    }
    public function scopeTripSearchStartEndDate($query, $start_date, $end_date)
    {
        return $query->whereRaw('concat(date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE shedule_pickup_time END) >="' . $start_date . '"')
            ->WhereRaw('concat(date_of_service," ", CASE WHEN shedule_pickup_time IS NULL THEN "00:00:00" ELSE shedule_pickup_time END) <="' . $end_date . '"');
    }
}
