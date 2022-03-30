<?php

namespace App\Http\Controllers\Franchise;

use DB;
use Carbon\Carbon;
use App\Models\Crm;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\CrmResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\CrmCollection;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\facilityStoreRequest;
use App\Http\Requests\updateFacilityRequest;
use App\Exports\TripExport;

class CrmController extends Controller
{

  
    /*---------------------crm List---------------- */

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
            'payor_type_id' => 'required|exists:payor_types,id',
        ]);

            if ($validator->fails()) {
                $metaData = metaData('false', $request, '4006', '', '502', '', $validator->messages());
                return response()->json($metaData, 200);
            }
            $query = Crm::where('type', $request['payor_type_id'])->where('user_id', $request['eso_id']);
            $crmList= Crm::filterCrmList($request->all(), $query);
            $crmList=$crmList->latest()->paginate(config('Settings.pagination'));
            return new CrmCollection($crmList);
        } catch (\Exception $e) {
            return metaData(false, $request, 30001, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------End crm List---------------- */

    /*--------------------- Add crm---------------- */

    public function store(facilityStoreRequest $request)
    {
        $request->merge(['name_city' => $request->name.'_'.$request->city]);
        try {
            $crm=Crm::Create($request->all());

            $departments = $request->department;

            foreach ($departments as $key => $department) {
                $crm_department = new Department;
                $crm_department->name = $department;
                $crm_department->crm_id = $crm->id;
                $crm_department->save();
            }
            $metaData= metaData(true, $request, '4002', 'success', 200, '');
            return (new CrmResource($crm))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, 30002, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    /*---------------------End Add crm---------------- */

    /*---------------------Update crm---------------- */

    public function update(updateFacilityRequest $request)
    {
        try {
            $request->merge(['name_city' => $request->name.'_'.$request->city]);
            $input=$request->except('eso_id');
        
            $crm=Crm::find($request->id);
            $crm->update($input);

            $departments = $request->department;
            $ids = $request->department_id;

            foreach ($departments as $key => $department) {
                if (isset($ids[$key])) {
                    $crm_department = Department::find($ids[$key]);
                } else {
                    $crm_department = new Department;
                }
                $crm_department->name = $department;
                $crm_department->crm_id = $crm->id;
                $crm_department->save();
            }
            
            $metaData= metaData(true, $request, '4003', 'success', 200, '');
            return (new CrmResource($crm))->additional($metaData);
        } catch (\Exception $e) {
            return metaData(false, $request, '4003', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    /*---------------------End Update crm---------------- */

    /*---------------------edit Auto crm---------------- */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:crm,id,deleted_at,NULL',
            
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);

        if ($validator->fails()) {
            return $metaData = metaData('false', $request, '4003', '', '502', '', $validator->messages());
        }
        
        try {
            $crm=Crm::with('departments')->findOrFail($request->id);
            $metaData= metaData(true, $request, '4003', 'success', 200, '');
            return (new CrmResource($crm))->additional($metaData);
            ;
        } catch (\Exception $e) {
            return metaData(false, $request, 30009, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    

    /*------------------------End edit crm---------------- */

    /*---------------------delete crm---------------- */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' =>  ['required', Rule::exists('crm', 'id,deleted_at,NULL')->where('user_id', esoId())],
            
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        
        if ($validator->fails()) {
            return metaData('false', $request, '4005', '', '502', '', $validator->messages());
        }

        try {
            $dep  = Crm::find($request->id);
            $dep->departments()->delete();
            Crm::find($request->id)->delete();
            $metaData=metaData(true, $request, '4005', 'success', 200, '');
            return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '4005', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    
    /*---------------------End Delete crm---------------- */


    /*---------------------delete Department---------------- */
    public function destroyDepartments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:crm_departments,id,deleted_at,NULL',
            
        ], [
            'id.required' => 'ID is required.',
            'id.exists' => 'Invalid ID',
            
        ]);
        
        if ($validator->fails()) {
            return metaData('false', $request, '4006', '', '502', '', $validator->messages());
        }

        try {
            Department::find($request->id)->delete();
            $metaData=metaData(true, $request, '4006', 'success', 200, '');
            return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
        } catch (\Exception $e) {
            return metaData(false, $request, '4006', '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }
    
    public function crmWithDepartments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payor_type_id' => ['required',  Rule::exists('payor_types', 'id')->where(function ($query) {
                return $query->whereNotIn('id', [1,3]);
            }),
                ],
        ]);

        if ($validator->fails()) {
            $metaData = metaData('false', $request, '10006', '', '502', '', $validator->messages());
            return response()->json($metaData, 200);
        }

        
        $payors = Crm::with('departments:id,name,crm_id')->select('id', 'name')->eso()->get();

        return CrmResource::collection($payors)->additional(metaData(true, $request, '1008', 'success', '', '', ''));
    }

    public function crmDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            'crm_id' => ['required', Rule::exists('crm', 'id')->where('user_id', esoId())->whereNull('deleted_at')]
        ]);

        if ($validator->fails()) {
            $metaData = metaData('false', $request, '10006', '', '502', '', $validator->messages());
            return response()->json($metaData, 200);
        }

        $payor = Crm::with('departments:id,name,crm_id')->select('id', 'name')->find($request->crm_id);
        return (new CrmResource($payor))->additional(metaData(true, $request, '1008', 'success', '', '', ''));
    }

    /*---------------------End Delete Department---------------- */
}
