<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Timezone;
class ZoneZip extends Model
{
    use HasFactory,Timezone ,SoftDeletes;
    protected $table = 'zone_zips';
    public function zoneName()
    {
        return $this->belongsTo(ZoneMaster::class, 'zone_id');
    }
    public function zip()
    {
        return $this->hasMany(ZoneZip::class, 'zone_id');
    }
}
