<?php

namespace App\Http\Controllers\Franchise;
use DB;
use Illuminate\Http\Request;
use App\Models\ProviderMaster;
use App\Http\Controllers\Controller;
use App\Http\Requests\BrokerRequest;
use App\Http\Resources\BrokerResource;
use App\Http\Resources\BrokerCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UpdateBrokerRequest;

class BrokerController extends Controller
{
    public function index(Request $request)
    {
        $provider = ProviderMaster::eso()->paginate(20);
     return  $response = new BrokerCollection($provider);
    }

    public function temp(){
        $broker =  DB::table('provider_templates')->select('id', 'name')
        ->orderBy('name', 'ASC')
        ->get();
        return $broker;
    }
    
    public function store(BrokerRequest $request){
    
        try {
            $request-> merge(['user_id' => $request->eso_id]);
            $request-> merge(['name_city' => $request->name.'_'.$request->city]);
            $provider = ProviderMaster::create($request->all());
            $metaData= metaData(true, $request, '5001', 'added successfully', 200, '');
            return (new BrokerResource($provider))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 5001, '', 502, errorDesc($e), 'Error occured in server side');
        }

    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:provider_master,id,deleted_at,NULL',
            
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '50001', '', '500', '', $validator->messages());
        }
        
        try {
            $provider=ProviderMaster::where(['user_id' => $request->eso_id])->where('id',$request->id)->first();
            $metaData= metaData(true, $request, '3001', 'success', 200, '');
            return (new BrokerResource($provider))->additional($metaData);
            ;
        } catch (\Exception $e) {
            return metaData(false, $request, 50001, '', 500, errorDesc($e), 'Error occured in server side ');
        }
		
    }

    public function update(UpdateBrokerRequest $request)
    {   
        

        try {
            $request-> merge(['user_id' => $request->eso_id]);
            $request-> merge(['name_city' => $request->name.'_'.$request->city]);
            $input=$request->except('eso_id');
            $provider=ProviderMaster::find($request->id);
            $provider->update($input);
            $metaData= metaData(true, $request, '5002', 'success', 503, '');
            return (new BrokerResource($provider))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '5002', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
     
   
    public function brokerList(Request $request)
    {
        try {
            $query = ProviderMaster::where('user_id', $request['eso_id']);
            $provider= ProviderMaster::filterBrokerList($request->all(), $query);
            $provider=$query->latest()->paginate(config('Settings.pagination'));
            return new BrokerCollection($provider);
        } catch (\Exception $e) {
            return metaData(false, $request, 5003, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }


    

    public function destroy(Request $request)
    {    
        $validator = Validator::make($request->all(), [
        'id' => 'required|numeric|exists:provider_master,id,deleted_at,NULL',
        
         ], [
        'id.required' => 'ID is required.',
        'id.exists' => 'Invalid ID',
        
        ]);
    
        if ($validator->fails()) {
        return metaData('false', $request, '5004', '', '504', '', $validator->messages());
        }

        try {
            $provider = ProviderMaster::findorFail($request->id);
            ProviderMaster::find($request->id)->delete();
            $metaData=metaData(true, $request, '5004', 'success', 200, '');
             return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '5004', '', 502, errorDesc($e), 'Error occured in server side ');
        }
       
    }
}
