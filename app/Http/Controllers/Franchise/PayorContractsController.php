<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\PayorContract;
use App\Models\PayorContractMethod;
use App\Models\PayorServiceRate;
use Illuminate\Http\Request;
use App\Http\Resources\PayorServiceResource;
use App\Http\Requests\PayorContractStoreRequest;
use App\Http\Requests\PayorContractUpdateRequest;

class PayorContractsController extends Controller
{
    public static function store(PayorContractStoreRequest $request)
    {
        try {
        $contract_names = $request->contract_name;
        foreach ($contract_names as $location => $contract_name) {
            $data_rates = array(
                'payor_type' => $request->payor_type,
                'payor_id' => $request->payor_id,
                'name' => $contract_name,
                'level_of_service' => $request['service_id'][$location],
                'county_type' => $request['county'][$location],
                'default' => $request['default'][$location]
            );
            $payor_contracts = PayorContract::create($data_rates);
            if (isset($request['select_amount'][$location])) {
                $select_amount = $request['select_amount'];
                foreach ($select_amount as $method) {
                    $contracts_methods = array(
                        'payor_type' => $request->payor_type,
                        'payor_id' => $request->payor_id,
                        'contract_id' => $payor_contracts->id,
                        'method_id' => $method
                    );
                    PayorContractMethod::create($contracts_methods);
                }
            }
        }
        return metaData(true, $request, '4009', 'Added successfully', 200, '');
    } catch (\Exception $e) {
        return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
    }
}
    public static function update(PayorContractStoreRequest $request)
    {
        try {
        $contract_names = $request->contract_name;
        foreach ($contract_names as $location => $contract_name) {
            $data_rates = array(
                'payor_type' => $request->payor_type,
                'payor_id' => $request->payor_id,
                'name' => $contract_name,
                'level_of_service' => $request['service_id'][$location],
                'county_type' => $request['county'][$location],
                'default' => $request['default'][$location]
            );
            $checkPayorContract = PayorContract::find($request['payorContractId'][$location]);
            if($checkPayorContract){
                $checkPayorContract->update($data_rates);
            }else{
                $checkPayorContract = PayorContract::create($data_rates);
            }
            if (isset($request['select_amount'][$location])) {
                $select_amount = $request->select_amount;
                foreach ($select_amount as $method) {
                    $contracts_methods = array(
                        'payor_type' => $request->payor_type,
                        'payor_id' => $request->payor_id,
                        'contract_id' => $checkPayorContract->id,
                        'method_id' => $method
                    );
                    $checkPayorContractMethod = PayorContractMethod::find($request['payorContractMethodId'][$location]);
                    if($checkPayorContractMethod){
                        $checkPayorContractMethod->update($contracts_methods);
                    }else{
                        PayorContractMethod::create($contracts_methods);
                    }
                }
            }
        }
        return metaData(true, $request, '4010', 'Updated successfully', 200, '');
    } catch (\Exception $e) {
        return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
    }
    
}
}
