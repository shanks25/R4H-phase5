<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripListingCollection;
use App\Http\Requests\CommonFilterRequest;
use App\Traits\TripTrait;

class DriverLogsController extends Controller
{
    use TripTrait;
    public function index(CommonFilterRequest $request) 
    {
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no,VIN',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'log',
            'statuslog:id,driver_id,trip_id,status,timezone,date_time',
            'member:id,name',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        try {
            $payorlog =  $this->trips($request, $with_array)->latest()->paginate(config('Settings.pagination'));
            return (new TripListingCollection($payorlog));
        } catch (\Exception $e) {
            return metaData(false, $request, '4015', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

}
