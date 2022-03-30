<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LevelofService;

class DriverLevelofService  extends Model
{
    use HasFactory;
    protected $table = "driver_level_of_service";
    public $timestamps = false;
}
