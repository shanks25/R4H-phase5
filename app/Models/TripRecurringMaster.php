<?php

namespace App\Models;

use App\Traits\Timezone;
use App\Traits\LocalScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripRecurringMaster extends Model
{
    use HasFactory,Timezone,LocalScopes, SoftDeletes;
    
    protected $table = 'trip_recurring_master';
    protected $fillable = ['days','start_date','end_date','master_key','primary_trip_id','trip_count','user_id','weekdays'];


    public function trips()
    {
        return $this->hasMany(TripMaster::class, 'recurring_master_id');
    }

    // deleting assgined and unassigned
    public function IncompleteTrips()
    {
        return $this->hasMany(TripMaster::class, 'recurring_master_id')->whereIn(
            'status_id',
            [1,2]
        );
    }
}
