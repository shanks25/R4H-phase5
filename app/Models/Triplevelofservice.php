<?php

namespace App\Models;

use App\Traits\Timezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Triplevelofservice extends Model
{
    use HasFactory,Timezone;
    protected $table = 'trip_level_of_service';
}
