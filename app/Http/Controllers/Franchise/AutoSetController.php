<?php

namespace App\Http\Controllers\Franchise;

use DB;
use stdClass;
use Carbon\Carbon;

use App\Models\AutoSet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\ValidatePayorIdRule;
use App\Http\Controllers\Controller;
use Facade\FlareClient\Http\Response;
use App\Http\Resources\AutoSetResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\AutoSetCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ValidateAutoSetRequest;

class AutoSetController extends Controller
{
    
    /*---------------------Auto Set List---------------- */

    public function index(Request $request)
    {
        try {
            $query = AutoSet::eso()->with('payorTypeNames:id,name')->with('payor:id,name');
            $autoset= AutoSet::filterAutoSet($request, $query);
            $autoset=$autoset->latest()->paginate(config('Settings.pagination'));
            return  new AutoSetCollection($autoset);
        } catch (\Exception $e) {
            return metaData(false, $request, 30007, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------End Auto Set List---------------- */
    
    /*--------------------- Add Auto Set---------------- */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payor_type' =>  ['required', 'numeric',Rule::exists('payor_types', 'id') ],
            'payor_id' =>  ['required', 'numeric', new ValidatePayorIdRule()],
            'auto_set_time' => 'required|numeric',
            
        ]);
        
        if ($validator->fails()) {
            return   metaData(false, $request, '30008', '', $error_code = 502, '', $validator->messages());
        }
        $payble_type="";
        if ($request->payor_type==3) {
            $payble_type='App\Models\ProviderMaster';
        } elseif ($request->payor_type==2) {
            $payble_type='App\Models\Facility';
        } else {
            $payble_type='App\Models\Crm';
        }

        $request-> merge(['payable_type' => $payble_type]);
     
                
        try {
            $autoset=AutoSet::Create($request->all());
            $metaData= metaData(true, $request, '3008', 'success', 200, '');
            return (new AutoSetResource($autoset))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------End Add Auto Set---------------- */
    

    /*---------------------edit Auto Set---------------- */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('auto_sets', 'id,deleted_at,NULL')->where('user_id', esoId())],
            
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '30009', '', '502', '', $validator->messages());
        }
        
        try {
            $autoset=AutoSet::find($request->id);
            $metaData= metaData(true, $request, '3009', 'success', 200, '');
            return (new AutoSetResource($autoset))->additional($metaData);
            ;
        } catch (\Exception $e) {
            return metaData(false, $request, 30009, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    

    /*------------------------End edit vehicle---------------- */


    /*---------------------Update vehicle---------------- */

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('auto_sets', 'id,deleted_at,NULL')->where('user_id', esoId())],
            'auto_set_time' => 'required|numeric',
            
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        
          

        if ($validator->fails()) {
            return   metaData(false, $request, '3010', '', $error_code = 502, '', $validator->messages());
        }
        
        
            
        try {
            $request-> merge(['updated_at' => now()]);
          
            $input=$request->except('eso_id');
        
            $autoset=AutoSet::find($request->id);
            $autoset->update($input);
            $updatedautoset=AutoSet::find($request->id);
            
            $metaData= metaData(true, $request, '3010', 'success', 200, '');
            return (new AutoSetResource($updatedautoset))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '3010', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    /*---------------------End Update Auto Set---------------- */


    /*---------------------delete Auto Set---------------- */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('auto_sets', 'id,deleted_at,NULL')->where('user_id', esoId())],
            
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        
        if ($validator->fails()) {
            return metaData('false', $request, '3011', '', '502', '', $validator->messages());
        }

        try {
            AutoSet::find($request->id)->delete();
            $metaData=metaData(true, $request, '3011', 'success', 200, '');
            return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '3011', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    

    public function payorWiseAutoSet(ValidateAutoSetRequest $request)
    {
        try {
            $autoset=AutoSet::where('payor_type', $request->payor_type)->where('payor_id', $request->payor_id)->first();
          
            if (!$autoset) {
                $data['data'] =new stdClass();
            
                $metaData= metaData(true, $request, '3009', 'auto set not does not exist', 200, '');
                return   merge($data, $metaData);
            }
            
            $metaData= metaData(true, $request, '3009', 'success', 200, '');
            return (new AutoSetResource($autoset))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30009, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    /*---------------------End Delete Auto Set---------------- */
}
