<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripListingCollection;
use App\Traits\TripTrait;
use App\Http\Requests\CommonFilterRequest;
use App\Models\StatusMaster;
use Illuminate\Http\Request;

class TripPaymentHistoryController extends Controller
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
            'relInvoiceItem:id,trip_id,total_amount,provider_invoice_status_id',
            'statuslog:id,driver_id,trip_id,status,timezone,date_time',
            'member:id,name,email,phone_number,first_name,middle_name,last_name,gender',
            'zone:zone_id,zipcode', 'zone.zoneName:id,name',
        ];
        try {
            $payorlog =  $this->trips($request, $with_array)->latest()->paginate(config('Settings.pagination'));
            // return $payorlog;
            return (new TripListingCollection($payorlog));
        } catch (\Exception $e) {
            return metaData(false, $request, '4032', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function remittancStatus(Request $request){
        $main['data'] =  ['', 'Unpaid', 'Paid', 'Partially Paid','Multiple', 'NA'];
        $metaData= metaData(true, $request, '4033', 'success', 200, '');
        return merge($metaData, $main);
    }

}
