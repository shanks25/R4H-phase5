<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripLog extends Model
{
    use \Awobaz\Compoships\Compoships;
    use HasFactory;
    protected $guarded = [];
}
