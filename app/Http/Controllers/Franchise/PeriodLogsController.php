<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeriodLogCollection;
use App\Traits\TripTrait;
use App\Http\Requests\CommonFilterRequest;

class PeriodLogsController extends Controller
{
   
    use TripTrait;
    public function index(CommonFilterRequest $request) 
    {
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no,VIN',
            'payorTypeNames:id,name',
            'baselocation:id,name',
            'log',
        ];
        try {
            $payorlog =  $this->trips($request, $with_array)->where('status_id',3)->latest()->paginate(config('Settings.pagination'));
            return (new PeriodLogCollection($payorlog));
        } catch (\Exception $e) {
            return metaData(false, $request, '4016', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

}
