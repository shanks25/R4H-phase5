<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripPayoutDetail extends Model
{
    use HasFactory;
    protected $table = "trip_payout_detail";
    protected $guarded = [];
}
