<?php

namespace App\Models;

use App\Traits\Timezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelofServiceBufferTime extends Model
{
    use HasFactory, Timezone;
    protected $table = "level_of_service_buffer_time";
}
