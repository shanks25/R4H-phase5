<?php

namespace App\Http\Controllers\Franchise;

use DB;
use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\OdoMeter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\VehicleLevelofService;
use Facade\FlareClient\Http\Response;
use App\Http\Resources\VehicleResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\VehicleCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\VehicleStoreRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\VehicleUpdateRequest;

class VehicleController extends Controller
{

    /*---------------------vehicle List---------------- */

    public function index(Request $request)
    {
        try {
            $query = Vehicle::eso()->with('masterLevelservices:id,name');
            $vehicle=Vehicle::filterVehicle($request, $query)
            ->latest()->paginate(config('Settings.pagination'));
            return new VehicleCollection($vehicle);
        } catch (\Exception $e) {
            return metaData(false, $request, 30001, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------End vehicle List---------------- */
    
    /*--------------------- Add vehicle---------------- */

    public function store(VehicleStoreRequest $request)
    { 
     
        
        
        $upload_documents_path = '';
            
        if ($request->hasFile('documents_file')) {
            $upload_documents_path =   upload($request->file('documents_file'), '/storage/uploads/vehicle');
        }
          
         $request-> merge(['documents' => $upload_documents_path ]);
       
        try {
            $vehicle=Vehicle::Create($request->all());
            $result	=	$vehicle->masterLevelservices()->attach($request->service_id);

            OdoMeter::Create([
                        'vehicle_id'=>$vehicle->id,
                        'driver_id'=>'0',
                        'odometer'=>$request->odometer,
                        'date_of_service'=>$request->odometer_start_date
                        ]);

            $newVehicle=Vehicle::find($vehicle->id);
            $metaData= metaData(true, $request, '3002', 'success', 200, '');
            return (new VehicleResource($newVehicle))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------End Add vehicle---------------- */
    

    /*---------------------edit vehicle---------------- */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'id' => ['required', Rule::exists('vehicle_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
                
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30003', '', '502', '', $validator->messages());
        }
        
        try {
            $vehicle=Vehicle::find($request->id);
            $metaData= metaData(true, $request, '30003', 'success', 200, '');
            return (new VehicleResource($vehicle))->additional($metaData);
            
        } catch (\Exception $e) {
            return metaData(false, $request, 30003, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    

    /*------------------------End edit vehicle---------------- */


    /*---------------------Update vehicle---------------- */

    public function update(VehicleUpdateRequest $request)
    {
       
        
        $upload_documents_path = '';
            
        if ($request->hasFile('documents_file')) {
            $upload_documents_path =   upload($request->file('documents_file'), '/storage/uploads/vehicle');
            $request-> merge(['documents' => $upload_documents_path ]);
        }
    
        try {
            $request-> merge(['updated_at' => now()]);
            $input=$request->except('documents_file', 'service_id', 'eso_id');
        
            $vehicle=Vehicle::where('id', $request->id)->update($input);
            $vehicles=Vehicle::find($request->id)->masterLevelservices()->sync($request->service_id);
                
            $updatedVehicle=Vehicle::find($request->id);
            $metaData= metaData(true, $request, '3004', 'success', 200, '');
            return (new VehicleResource($updatedVehicle))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '30004', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    /*---------------------End Update vehicle---------------- */


    /*---------------------delete vehicle---------------- */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
         'id' => ['required', Rule::exists('vehicle_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())]   
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);

        if ($validator->fails()) {
            return metaData('false', $request, '30003', '', '502', '', $validator->messages());
        }

        try {
            Vehicle::find($request->id)->masterLevelservices()->where('vehicle_id', $request->id)->detach();
            Vehicle::find($request->id)->delete();
            $metaData=metaData(true, $request, '3004', 'success', 200, '');
            return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '30005', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    
    /*---------------------End edit vehicle---------------- */

    public function updateOdometer(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'vehicle_id' => ['required', Rule::exists('vehicle_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
            'odometer' => 'required|numeric',
           
        ], [
            'vehicle_id.required' => 'Vehicle ID is required.',
            'odometer.required' => 'OdoMeter value is required.',
        ]);

        if ($validator->fails()) {
            return metaData('false', $request, '30006', '', '502', '', $validator->messages());
        }

        try {
                    
          
            $oldOdoMeter=OdoMeter::where('vehicle_id', $request->vehicle_id)->latest()->first();
            $trip_reading=0;
            if ($oldOdoMeter) {
                $last_second_odometer = OdoMeter::where('vehicle_id', $request->edit_odo_vehicle_id)->latest()->skip(1)->take(1)->first();
                if ($last_second_odometer) {
                    $trip_reading = $request->odometer - $last_second_odometer->odometer;
                }

                OdoMeter::where('id', $oldOdoMeter->id)->update(['odometer'=>$request->odometer,'trip_reading'=>$trip_reading]);

                $metaData=metaData(true, $request, '3006', 'success', 200, '');

                return  merge($metaData, ['data'=>['vehicle_id'=>$request->vehicle_id,'updated_odometer'=>$request->odometer]]);
            } else {
                return metaData(false, $request, '30006', '', 502, '', 'Failed! try again later. ');
            }
        } catch (\Exception $e) {
            return metaData(false, $request, '30006', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }


   
}
