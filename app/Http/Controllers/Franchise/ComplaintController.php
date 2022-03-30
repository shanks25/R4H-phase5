<?php

namespace App\Http\Controllers\Franchise;
use DB;
use App\Models\Complaint;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Http\Requests\ComplaintStoreRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ComplaintCollection;

class ComplaintController extends Controller
{
    public function complaintstore(ComplaintStoreRequest $request)
    {
   
       try {
        $upload_documents_path = '';
            
        if ($request->hasFile('upload_file')) {
            $upload_documents_path =   upload($request->file('upload_file'), '/storage/uploads/vehicle');
        }
            $request-> merge(['user_id' => $request->eso_id]);
            $request-> merge(['upload' => $upload_documents_path]); 
            $complaint=Complaint::create($request->all());
            $metaData= metaData(true, $request, '5001', 'added successfully', 200, '');
            return (new ComplaintResource($complaint))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 5001, '', 502, errorDesc($e), 'Error occured in server side');
        } 
    }
   

   public function complaintdestroy(Request $request)
   {
    $validator = Validator::make($request->all(), [
        'id' => 'required|numeric|exists:complaint_driver,id,deleted_at,NULL',
        
        
         ], [
        'id.required' => 'ID is required.',
        'id.exists' => 'Invalid ID',
        
        ]);
    
        if ($validator->fails()) {
            return metaData('false', $request, '5004', '', '504', '', $validator->messages());
            }

        try {
            $complaint = Complaint::findorFail($request->id);
            Complaint::find($request->id)->delete();
            $metaData=metaData(true, $request, '5004', 'success', 200, '');
             return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '5004', '', 502, errorDesc($e), 'Error occured in server side ');
        }
   }

   public function complaintList(Request $request)
   {
    try {
        $query = Complaint::where('user_id', $request['eso_id']);
        $complaint= Complaint::filtercomplaintList($request->all(), $query);
        $complaint=$query->latest()->paginate(config('Settings.pagination'));
        return new ComplaintCollection($complaint);
    } catch (\Exception $e) {
        return metaData(false, $request, 5003, '', 502, errorDesc($e), 'Error occured in server side ');
    }
   }

}
