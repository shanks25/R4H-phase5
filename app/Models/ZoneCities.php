<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Timezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoneCities extends Model
{
    use HasFactory, Timezone, SoftDeletes;

    protected $table = 'zone_cities';
    protected $guarded = [];
    public $timestamps = false;
    
    public function city()
    {
        return $this->belongsTo('App\Models\CountyCities', 'city_id');
    }
    
}
