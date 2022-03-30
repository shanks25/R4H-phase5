<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripListingCollection;
use App\Http\Resources\TripStatusCollection;
use App\Traits\TripTrait;
use App\Models\TripMaster;
use App\Http\Requests\CommonFilterRequest;
use App\Models\StatusMaster;
use Illuminate\Http\Request;

class RestoreTripController extends Controller
{
    use TripTrait;
    public function index(CommonFilterRequest $request)
    {
        $with_array = [
            'driver:id,name,vehicle_id',
            'vehicle:id,model_no,VIN,vehicle_model_type',
            'status:id,status_description',
            'payorTypeNames:id,name',
            'payor:id,name,phone_number',
            'levelOfService:id,name',
            'baselocation:id,name',
            'importFile:id,imported_file',
            'log',
            'statuslog:id,driver_id,trip_id,status,timezone,date_time',
            'member:id,name,email,phone_number,first_name,middle_name,last_name,gender',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
            'member:id,name,email,mobile_no,first_name,middle_name,last_name,gender',
        ];
        try {
            $payorlog =  $this->trips($request, $with_array)->latest()->onlyTrashed()->paginate(config('Settings.pagination'));
            // return $payorlog;
            return (new TripListingCollection($payorlog));
        } catch (\Exception $e) {
            return metaData(false, $request, '4032', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function restorTrips(Request $request)
    {
        $ids = $request->id;

        $cnt = 0;
        $dup = 0;
        try {
            foreach ($ids as $key => $trip) {
                $del_trip = TripMaster::select('id', 'trip_no')->where('id', $trip)->withTrashed()->first();
                $TripId = $del_trip->trip_no;

                $tripid_dup = TripMaster::where('trip_no', $TripId)->where('id', '!=', $trip)->first();
                if (!$tripid_dup) {
                    TripMaster::where('id', $trip)->withTrashed()->restore();
                    $cnt++;
                } else {
                    $dup++;
                }
            }
            // print_r($TripId);die;

            if ($cnt) {
                $data['msg'] = 'Total ' . $cnt . ' Trips are restored successfully.';
                if ($dup > 1) {
                    $data['msg'] = 'Total ' . $dup . ' Trip ID already exists. Trip cannot be restored.';
                } elseif ($dup == 1) {
                    $data['msg'] = 'Trip ID already exists. Trip cannot be restored.';
                }

                
            }else{
                $data['msg'] = 'Trip ID already exists. Trip cannot be restored.';
            }

            $metaData = metaData(true, $request, '4036', 'success', 200, '');
                return merge($data, $metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '4032', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
}
