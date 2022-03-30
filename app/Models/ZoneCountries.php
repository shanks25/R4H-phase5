<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Timezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoneCountries extends Model
{
    use HasFactory,Timezone,SoftDeletes;
    
    protected $table = 'zone_counties';
    protected $guarded  = [];
    public $timestamps = false;

    public function county()
    {
        return $this->belongsTo(ZoneMaster::class, 'zone_id');
    }
     public function state()
    {
        return $this->hasMany(ZoneStates::class, 'state_id');
    }
}
