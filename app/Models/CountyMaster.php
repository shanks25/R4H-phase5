<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountyMaster extends Model
{
    use HasFactory;

    protected $table = 'county_master';
    protected $guarded = [];
    public $timestamps = false;
    
}
