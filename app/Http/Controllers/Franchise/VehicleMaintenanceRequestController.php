<?php

namespace App\Http\Controllers\Franchise;

use Illuminate\Http\Request;
use App\Models\VehicleInvoice;
use Illuminate\Validation\Rule;
use App\Models\VehicleMaintenance;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\VehicleMaintenanceResource;
use App\Http\Resources\VehicleMaintenanceCollection;
use App\Http\Requests\StoreVehicleMaintenanceRequest;
use App\Http\Requests\UpdateVehicleMaintenanceRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Garage;

class VehicleMaintenanceRequestController extends Controller
{
    public function index(Request $request)
    {
        try {
            return new VehicleMaintenanceCollection(VehicleMaintenance::with(['vehicle:id,model_no,VIN', 'driver:id,name', 'vehicleMaintenanceService:id,name', 'invoices'])->latest()->paginate(config('Settings.pagination')));
        } catch (\Exception $e) {
            return metaData(false, $request, 30001, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------End Vehicle Maintenance request  List---------------- */


    /*--------------------- Add Vehicle Maintenance request ---------------- */
    public function store(StoreVehicleMaintenanceRequest $request)
    {
        try {
            if (!in_array(13, $request->service_details)) {
              //  $request->merge(['other_details' => '']);
                $request->merge(['other_service_details' => '']);
            }
            $vehicleMaintenanceRequest = VehicleMaintenance::create($request->all());
            $vehicleMaintenanceRequest->ticket_id  = 'ID' . createTicketNo($vehicleMaintenanceRequest->id);
            $vehicleMaintenanceRequest->save();
            $vehicleMaintenanceRequest->vehicleMaintenanceService()->sync($request->service_details);
            $metaData = metaData(true, $request, '3002', 'success', 200, '');
            return (new VehicleMaintenanceResource($vehicleMaintenanceRequest))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    /*---------------------End Add Vehicle Maintenance Request---------------- */



    /*---------------------edit Vehicle Maintenance Request---------------- */
    public function edit(Request $request)
    {
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('vehicle_maintenance_requests', 'id,deleted_at,NULL')->where('user_id', esoId())],
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
        ]);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30003', '', '502', '', $validator->messages());
        }
        try {
            $vehicleMaintenanceRequest = VehicleMaintenance::find($request->id);
            $metaData = metaData(true, $request, '30003', 'success', 200, '');
            return (new VehicleMaintenanceResource($vehicleMaintenanceRequest))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30003, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    /*------------------------End edit Vehicle Maintenance Request---------------- */


 /*------------------------start update Vehicle Maintenance Request---------------- */
    public function update(UpdateVehicleMaintenanceRequest $request)
    {
        try {
            
                if (!in_array(13, $request->service_details)) {
                    //  $request->merge(['other_details' => '']);
                    $request->merge(['other_service_details' => '']);
                }
            $input = $request->except('eso_id','service_details');

            $vmr = VehicleMaintenance::where('id', $request->id)->update($input);
            $vmr = VehicleMaintenance::find($request->id)->vehicleMaintenanceService()->sync($request->service_details);

            $updatedVehicle = VehicleMaintenance::find($request->id);
            $updatedVMR = VehicleMaintenance::find($request->id);
            $metaData = metaData(true, $request, '3004', 'success', 200, '');
            return (new VehicleMaintenanceResource($updatedVMR))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '30004', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    /*---------------------End Update Vehicle Maintenance Request---------------- */



    /*---------------------delete Vehicle Maintenance Request---------------- */
    public function destroy(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('vehicle_maintenance_requests', 'id,deleted_at,NULL')->where('user_id', esoId())]
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',

        ]);

        if ($validator->fails()) {
            return metaData('false', $request, '30003', '', '502', '', $validator->messages());
        }

        try {
            VehicleMaintenance::find($request->id)->vehicleMaintenanceService()->where('vehicle_maintenance_requests_id', $request->id)->detach();
            VehicleMaintenance::find($request->id)->delete();
            $metaData = metaData(true, $request, '3004', 'success', 200, '');
            return  merge($metaData, ['data' => ['deleted_id' => $request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '30005', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    
    /*---------------------End edit Vehicle Maintenance Request---------------- */


}
