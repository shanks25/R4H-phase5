<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\Vehicle;
use App\Models\AccidentImage;
use App\Models\AccidentPassenger;
use App\Models\DriverMaster;
use App\Models\TripMaster;
use App\helpers\Downloadshelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\AccidentCollection;
use App\Http\Requests\AccidentStoreRequest;
use Validator;
use App\Http\Resources\AccidentResource;
use Illuminate\Validation\Rule;
use App\Http\Requests\AccidentUpdateRequest;



class AccidentRequestController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Accident::eso();
            $accident=Accident::filterAccident($request, $query)
            ->latest()->paginate(config('Settings.pagination'));
            return new AccidentCollection($accident);
        } catch (\Exception $e) {
            return metaData(false, $request, 30001, '', 502, errorDesc($e), 'Error occured in server side');
        }

    }


    public function store(AccidentStoreRequest $request)
    {
        
        
        // $upload_documents_path = '';
            
        // if ($request->hasFile('documents_file')) {
        //     $upload_documents_path =   upload($request->file('documents_file'), '/storage/uploads/vehicle');
        // }
          
        //  $request-> merge(['documents' => $upload_documents_path ]);
       
        try {
            $accident=Accident::Create($request->all());

          
            $metaData= metaData(true, $request, '3002', 'success', 200, '');
            return (new AccidentResource($accident))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }


    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'id' => ['required', Rule::exists('accidents', 'id,deleted_at,NULL')->where('user_id', esoId())],
                
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30003', '', '502', '', $validator->messages());
        }
        
        try {
            $accident=Accident::find($request->id);
            $metaData= metaData(true, $request, '30003', 'success', 200, '');
            return (new AccidentResource($accident))->additional($metaData);
            
        } catch (\Exception $e) {
            return metaData(false, $request, 30003, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    

    /*------------------------End edit vehicle---------------- */


    /*---------------------Update vehicle---------------- */

    public function update(AccidentUpdateRequest $request)
    {
      
    
        try {
            $request-> merge(['updated_at' => now()]);
            $input=$request->except('video', 'accident_image', 'eso_id');
        
            $accident=Accident::where('id', $request->id)->update($input);
            // $accidents=Accident::find($request->id)->sync($request->service_id);
                
            $updatedAccident=Accident::find($request->id);
            $metaData= metaData(true, $request, '3004', 'success', 200, '');
            return (new AccidentResource($updatedAccident))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '30004', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }


    public function destroyold(Request $request)
    {
        $validator = Validator::make($request->all(), [
         'id' => ['required', Rule::exists('accidents', 'id,deleted_at,NULL')->where('user_id', esoId())]   
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);

        if ($validator->fails()) {
            return metaData('false', $request, '30003', '', '502', '', $validator->messages());
        }

        try {
            // Accident::find($request->id)->where('id', $request->id)->detach();
            Accident::find($request->id)->delete();
            $metaData=metaData(true, $request, '3004', 'success', 200, '');
            return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '30005', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function destroy(Request $request){

        {
            try {
                $accident_ids = array();
                if ($request->filled('accident_ids')) {
                    $accident_ids =  json_decode($request->accident_ids, true);
                } else {
                    return metaData(false, $request, 20006, '', 403, '', 'accident_ids is required');
                }
                if (count($accident_ids) > 0) {
                    $accidents = Accident::whereIn('id', $accident_ids)->get();

                    $filter_count = $accidents->count();
                   
                    $AccidentID = array();
                    foreach ($accidents as $key => $accident) {
                        $accident->delete();
                        $AccidentID[] = $accident->id;
                    }

                 
    
                  
                    $custom_array = ['deletedCount' => $filter_count];
                    $convert_array = ['data' => $custom_array];
                    $successs_msg = $filter_count . ' Accidents deleted successfully.';
                    $merged_array =  merge($convert_array, metaData(true, $request, 20006, $successs_msg, 502, '', $filter_count . ''));
                    return response()->json($merged_array);
                } else {
                    return metaData(false, $request, 20006, '', 403, '', 'accident_ids not found ');
                }
            } catch (\Exception $e) {
                return metaData(false, $request, 20006, '', 502, errorDesc($e), 'Error occured in server side ');
            }
        }
    }

    public function changestatus(Request $request){

        {
            try {
                $accident_ids = array();
                if ($request->filled('accident_ids')) {
                    $accident_ids =  json_decode($request->accident_ids, true);
                } else {
                    return metaData(false, $request, 20006, '', 403, '', 'accident_ids is required');
                }

                if (!$request->filled('status_id')) {
                    return metaData(false, $request, 20005, '', 403, '', 'status_id is required');
                }

                $status = $request->status_id;
                if (!in_array($status, [1,2,3])) {
                    return metaData(false, $request, 20005, '', 403, '', 'status_id not allowed');
                }
                if (count($accident_ids) > 0) {
                    $accidents = Accident::whereIn('id', $accident_ids)->get();

                    $filter_count = $accidents->count();
                   
                    $update_arr = array("status" => $status);

                    // $AccidentID = array();
                    // foreach ($accidents as $key => $accident) {
                    //     $accident->status= $update_arr;
                    //     $accident->update();
                    //     $AccidentID[] = $accident->id;
                    // }

                    $isupdate = Accident::whereIn('id', $accident_ids)->update($update_arr);

                 
    
                  
                    $custom_array = ['StatusChangeCount' => $filter_count];
                    $convert_array = ['data' => $custom_array];
                    $successs_msg = $filter_count . ' Accidents status updated successfully.';
                    $merged_array =  merge($convert_array, metaData(true, $request, 20006, $successs_msg, 502, '', $filter_count . ''));
                    return response()->json($merged_array);
                } else {
                    return metaData(false, $request, 20006, '', 403, '', 'accident_ids not found ');
                }
            } catch (\Exception $e) {
                return metaData(false, $request, 20006, '', 502, errorDesc($e), 'Error occured in server side ');
            }
        }
    }


}
