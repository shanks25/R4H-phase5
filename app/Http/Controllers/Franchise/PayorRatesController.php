<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\PayorContract;
use App\Models\PayorContractMethod;
use App\Models\PayorServiceRate;
use Illuminate\Http\Request;
use App\Http\Resources\PayorServiceResource;
use App\Http\Requests\PayorRateStoreRequest;
use App\Http\Requests\PayorRateUpdateRequest;

class PayorRatesController extends Controller
{
    public static function store(PayorRateStoreRequest $request)
    {
            try {
                $unloaded_array = $request['unloaded_rate_per_mile'];
                foreach ($unloaded_array as $l_of_s_id => $rates) {
                    $data_rates = array(
                        'payor_type' => $request['payor_type'],
                        'payor_id' => $request['payor_id'],
                        'level_of_service_id' => $l_of_s_id,
                        // Local start
                        //milage
                        'unloaded_rate_per_mile' => $request['unloaded_rate_per_mile'][$l_of_s_id],
                        'loaded_rate_per_mile' => $request['loaded_rate_per_mile'][$l_of_s_id],
        
                        //hourly
                        'unloaded_rate_per_hr' => $request['unloaded_rate_per_hr'][$l_of_s_id],
                        'loaded_rate_per_hr' => $request['loaded_rate_per_hr'][$l_of_s_id],
        
                        //base rate per hour
                        'base_rate' => $request['base_rate'][$l_of_s_id],
                        'unloaded_rate_per_hr_base' => $request['unloaded_rate_per_hr_base'][$l_of_s_id],
                        'loaded_rate_per_hr_base' => $request['loaded_rate_per_hr_base'][$l_of_s_id],
        
                        //base rate per mile
                        'base_rate_mileage' => $request['base_rate_mileage'][$l_of_s_id],//newcol
                        'base_rate_per_mile' => $request['base_rate_per_mile'][$l_of_s_id],//newcol
                        'loaded_rate_per_mile_base' => $request['loaded_rate_per_mile_base'][$l_of_s_id],
                        'loaded_rate_per_hr' => $request['loaded_rate_per_hr'][$l_of_s_id],
        
                        //flat rate
                        'flat_rate' => $request['flat_rate'][$l_of_s_id],
        
                        //other details
                        'wait_time_per_hour' => $request['wait_time_per_hour'][$l_of_s_id],
                        'minimum_payout' => $request['minimum_payout'][$l_of_s_id],
                        'insurance_rate_per_mile' => $request['insurance_rate_per_mile'][$l_of_s_id],
                        //local end here
        
                        
                        // Out of country
        
                        //mileage
                        'unloaded_rate_per_mile_out' => $request['unloaded_rate_per_mile_out'][$l_of_s_id],
                        'loaded_rate_per_mile_out' => $request['loaded_rate_per_mile_out'][$l_of_s_id],
        
                        //hourly
                        'unloaded_rate_per_hr_out' => $request['unloaded_rate_per_hr_out'][$l_of_s_id],
                        'loaded_rate_per_hr_out' => $request['loaded_rate_per_hr_out'][$l_of_s_id],
        
                        //base rate per hour
                        'base_rate_out' => $request['base_rate_out'][$l_of_s_id],
                        'unloaded_rate_per_hr_base_out' => $request['unloaded_rate_per_hr_base_out'][$l_of_s_id],
                        'loaded_rate_per_hr_base_out' => $request['loaded_rate_per_hr_base_out'][$l_of_s_id],
        
                        // flat rate
                        'flat_rate_out' => $request['flat_rate_out'][$l_of_s_id],//newcol
        
                         //other details
                        'wait_time_per_hour_out' => $request['wait_time_per_hour_out'][$l_of_s_id],
                        'minimum_payout_out' => $request['minimum_payout_out'][$l_of_s_id],
                        'insurance_rate_per_mile_out' => $request['insurance_rate_per_mile_out'][$l_of_s_id],
                    );
                    $status_service = PayorServiceRate::create($data_rates);
                }
                $metaData= metaData(true, $request, '4007', 'success', 200, '');
                return (new PayorServiceResource($status_service))->additional($metaData);
            } catch (\Exception $e) {
                return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
            }
    }
    public static function update(PayorRateStoreRequest $request)
    {
        
        try {
        $unloaded_array = $request['unloaded_rate_per_mile'];
        foreach ($unloaded_array as $l_of_s_id => $rates) {
            $data_rates = array(
                'payor_type' => $request['payor_type'],
                'payor_id' => $request['payor_id'],
                'level_of_service_id' => $l_of_s_id,
                // Local start
                //milage
                'unloaded_rate_per_mile' => $request['unloaded_rate_per_mile'][$l_of_s_id],
                'loaded_rate_per_mile' => $request['loaded_rate_per_mile'][$l_of_s_id],

                //hourly
                'unloaded_rate_per_hr' => $request['unloaded_rate_per_hr'][$l_of_s_id],
                'loaded_rate_per_hr' => $request['loaded_rate_per_hr'][$l_of_s_id],

                //base rate per hour
                'base_rate' => $request['base_rate'][$l_of_s_id],
                'unloaded_rate_per_hr_base' => $request['unloaded_rate_per_hr_base'][$l_of_s_id],
                'loaded_rate_per_hr_base' => $request['loaded_rate_per_hr_base'][$l_of_s_id],

                //base rate per mile
                'base_rate_mileage' => $request['base_rate_mileage'][$l_of_s_id],//newcol
                'base_rate_per_mile' => $request['base_rate_per_mile'][$l_of_s_id],//newcol
                'loaded_rate_per_mile_base' => $request['loaded_rate_per_mile_base'][$l_of_s_id],
                'loaded_rate_per_hr' => $request['loaded_rate_per_hr'][$l_of_s_id],
                'loaded_rate_per_mile_base' => $request['loaded_rate_per_mile_base'][$l_of_s_id],

                //flat rate
                'flat_rate' => $request['flat_rate'][$l_of_s_id],

                //other details
                'wait_time_per_hour' => $request['wait_time_per_hour'][$l_of_s_id],
                'minimum_payout' => $request['minimum_payout'][$l_of_s_id],
                'insurance_rate_per_mile' => $request['insurance_rate_per_mile'][$l_of_s_id],
                //local end here

                
                // Out of country

                //mileage
                'unloaded_rate_per_mile_out' => $request['unloaded_rate_per_mile_out'][$l_of_s_id],
                'loaded_rate_per_mile_out' => $request['loaded_rate_per_mile_out'][$l_of_s_id],

                //hourly
                'unloaded_rate_per_hr_out' => $request['unloaded_rate_per_hr_out'][$l_of_s_id],
                'loaded_rate_per_hr_out' => $request['loaded_rate_per_hr_out'][$l_of_s_id],

                //base rate per hour
                'base_rate_out' => $request['base_rate_out'][$l_of_s_id],
                'unloaded_rate_per_hr_base_out' => $request['unloaded_rate_per_hr_base_out'][$l_of_s_id],
                'loaded_rate_per_hr_base_out' => $request['loaded_rate_per_hr_base_out'][$l_of_s_id],

                // flat rate
                'flat_rate_out' => $request['flat_rate_out'][$l_of_s_id],//newcol

                 //other details
                'wait_time_per_hour_out' => $request['wait_time_per_hour_out'][$l_of_s_id],
                'minimum_payout_out' => $request['minimum_payout_out'][$l_of_s_id],
                'insurance_rate_per_mile_out' => $request['insurance_rate_per_mile_out'][$l_of_s_id],
            );
            $check_rates = PayorServiceRate::where('payor_id', $request['payor_id'])->where('level_of_service_id', $l_of_s_id)->first();
            if ($check_rates) {
                $status_service = PayorServiceRate::where('payor_id', $request['payor_id'])->where('level_of_service_id', $l_of_s_id)->update($data_rates);
            } else {
                $status_service = PayorServiceRate::create($data_rates);
            }
        }  
        print_r($status_service);die;
        return metaData(true, $request, '4008', 'updated successfully', 200, '');
    } catch (\Exception $e) {
        return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
    }
    }
   
}
