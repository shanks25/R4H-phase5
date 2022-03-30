<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverZones extends Model
{
    protected $table = "driver_zone";
    public $timestamps = false;
    public function zones()
    {
        return $this->belongsTo(ZoneMaster::class, 'zone_id', 'id');
    }
}
