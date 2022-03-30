<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Timezone;

class CountyCities extends Model
{
    use HasFactory,Timezone;
    protected $table = 'county_cities';
    protected $guarded  = [];
    public $timestamps = false;

    public function county()
    {
        return $this->hasMany(CountyNames::class, 'city_id')->orderBy('name', 'ASC');
    }
}
