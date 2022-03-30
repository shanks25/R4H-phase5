<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverUtilizationDetail extends Model
{
    use HasFactory;
    protected $table = "driver_utilization_detail";
    protected $guarded = [];
}
