<?php

namespace App\Models;

use App\Models\ZoneStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoneMaster extends Model
{

    use HasFactory, SoftDeletes;


    protected $table = 'zone_master';
    protected $guarded = [];

    public function county()
    {
        return $this->hasMany(ZoneCountries::class, 'zone_id');
    }
    public function zip()
    {
        return $this->hasMany(ZoneZip::class, 'zone_id');
    }
    public function state()
    {
        return $this->hasMany(ZoneStates::class, 'zone_id');
    }
    public function cities()
    {
        return $this->hasMany(ZoneCities::class, 'zone_id');
    }
   
}
