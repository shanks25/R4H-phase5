<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDeleteLog extends Model
{
    use HasFactory;
    protected $table = "trip_delete_log";
    protected $guarded = [];
}
