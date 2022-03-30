<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    
    protected $table = 'county_cities';
   
    public function county()
    {
        return $this->hasMany(CountyNames::class, 'city_id')->orderBy('name', 'ASC');
    }
}
