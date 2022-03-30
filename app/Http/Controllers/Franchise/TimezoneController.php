<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimezoneCollection;
use App\Models\TimezoneMaster;
use Illuminate\Http\Request;

class TimezoneController extends Controller
{
    public function index(Request $request)
    {
        $timezone = TimezoneMaster::OrderBy('long_name', 'ASC')->get();
        return new TimezoneCollection($timezone);
    }
}
