<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Timezone;

class CountyStates extends Model
{
    use HasFactory,Timezone;
    protected $table = 'county_states';
    protected $guarded = [];
    public $timestamps = false;
    
    public function state()
    {
        return $this->hasMany(ZoneStates::class, 'zone_id');
    }
}

