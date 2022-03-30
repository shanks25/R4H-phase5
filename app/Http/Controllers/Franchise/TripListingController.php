<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripListingCollection;
use App\Http\Resources\TripResource;
use App\Traits\TripTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use App\Models\TripMaster;
use Carbon\Carbon;

class TripListingController extends Controller
{
    use TripTrait;
    public function index(Request $request) //20001
    {
        try {
            return $this->tripsPaginationCollection($request);
        } catch (\Exception $e) {

            return metaData(false, $request, 20001, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
