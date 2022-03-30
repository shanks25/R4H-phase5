<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Timezone;

class ZoneStates extends Model
{
    use HasFactory,Timezone, SoftDeletes;
 
    protected $table = 'zone_states';
    protected $guarded  = [];

    public $timestamps = false;
    public function state()
    {
        return $this->belongsTo('App\Models\CountyStates', 'state_id');
    }
   

    // public function state()
    // {
    //     return $this->belongsTo(ZoneMaster::class, 'zone_id');
    // }

    
    
}
