<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountyNames extends Model
{
    use HasFactory;
    
    protected $table = 'county_names';
    
    public function zip()
    {
        return $this->hasMany(CountyMaster::class, 'county_id')->orderBy('county_name', 'ASC');
    }
}
