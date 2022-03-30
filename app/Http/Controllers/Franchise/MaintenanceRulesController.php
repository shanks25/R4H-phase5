<?php

namespace App\Http\Controllers\Franchise;
use Carbon\Carbon;
use App\Models\State;
use App\Models\Vehicle;
use App\Models\Category;
use App\Models\Facility;
use App\Models\PayorType;
use Illuminate\Http\Request;
use App\Models\ProviderMaster;
use App\Models\MaintenanceRule;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Resources\MaintenanceRulesResource;
use App\Http\Resources\MaintenanceRulesCollection;

class MaintenanceRulesController extends Controller
{
	public function index(Request $request)
	{
			$validator = Validator::make($request->all(), [
			'vehicle_id' => ['nullable', Rule::exists('vehicle_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())]], [
				'vehicle_id.required' => 'Invalid Vehicle.',
			]);
			if ($validator->fails()) {
				return $metaData = metaData('false', $request, '30040', '', '502', '', $validator->messages());
			}
		
		
		try {
		$rules = MaintenanceRule::eso()->with('vehicle:id,VIN,model_no')->with('vehicleRuleService:id,name');
	
		if ($request->filled('search')) {

			$search = $request->search;

			$rules->where(function ($q) use ($search) {
			return	$q->where('name', 'LIKE', '%' . $search . '%');
				$q->where('servicing_miles', 'LIKE', '%' . $search . '%');
			});
		}

		if ($request->filled('vehicle_id')) {
			$rules->where('vehicle_id', $request->vehicle_id);
		}

		$rules =	$rules->latest()->paginate(config('Settings.pagination'));
		return new MaintenanceRulesCollection($rules);
		} catch (\Exception $e) {
			return metaData(false, $request, 30040, '', 502, errorDesc($e), 'Error occured in server side ');
		}
	}


	

	public function edit(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'id' => ['nullable', Rule::exists('maintenance_rules', 'id,deleted_at,NULL')->where('user_id', esoId())]], [
				'id.required' => 'Invalid ID.',
			]);
			if ($validator->fails()) {
				return $metaData = metaData('false', $request, '30042', '', '502', '', $validator->messages());
			}
			try {

				$metaData= metaData(true, $request, '30042', 'success', 200, '');
				$rules =  MaintenanceRule::with('vehicle:id,VIN,model_no')->with('vehicleRuleService:id,name')->find($request->id);
				return (new MaintenanceRulesResource($rules))->additional($metaData);

			} catch (\Exception $e) {
				return metaData(false, $request, 30042, '', 502, errorDesc($e), 'Error occured in server side ');
			}
	
	
	}

	public function store(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'vehicle_id' => ['required', Rule::exists('vehicle_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
			'id' => ['nullable', Rule::exists('maintenance_rules', 'id,deleted_at,NULL')->where('user_id', esoId())],
			'service_id' =>'required|array|min:1',
			'notification_content' =>'required|max:150',
			'servicing_miles' =>'required|numeric|max:99999|not_in:0',
		
			], [
				'vehicle_id.required' => 'Invalid Vehicle.',
			]);
			if ($validator->fails()) {
				return $metaData = metaData('false', $request, '30043', '', '502', '', $validator->messages());
			}
		
		try {
				
		$rules = MaintenanceRule::Create($request->all());
		$rules->vehicleRuleService()->attach($request->service_id);
		$metaData= metaData(true, $request, '30043', 'success', 200, '');
		$rules =  MaintenanceRule::with('vehicle:id,VIN,model_no')->with('vehicleRuleService:id,name')->where('vehicle_id',$request->id)->first();
	
		return (new MaintenanceRulesResource($rules))->additional($metaData);

		} catch (\Exception $e) {
			return metaData(false, $request, 30043, '', 502, errorDesc($e), 'Error occured in server side ');
		}

	}


	public function update(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'vehicle_id' => ['required', Rule::exists('vehicle_master_ut', 'id,deleted_at,NULL')->where('user_id', esoId())],
			'id' => ['required', Rule::exists('maintenance_rules', 'id,deleted_at,NULL')->where('user_id', esoId())],
			'service_id' =>'required|array|min:1',
			'notification_content' =>'required|max:150',
			'servicing_miles' =>'required|numeric|max:99999|not_in:0',
		
			], [
				'vehicle_id.required' => 'Invalid Vehicle.',
			]);
			if ($validator->fails()) {
				return $metaData = metaData('false', $request, '30044', '', '502', '', $validator->messages());
			}
		
		try {

			$request-> merge(['updated_at' => now()]);	
			$rules = MaintenanceRule::find($request->id);
			$rules->update($request->except('id', 'eso_id','service_id'));
			$rules->vehicleRuleService()->sync($request->service_id);
			$rules =  MaintenanceRule::with('vehicle:id,VIN,model_no')
			->with('vehicleRuleService:id,name')->where('id',$request->id)->first();
			$metaData= metaData(true, $request, '30044', 'success', 200, '');
			return (new MaintenanceRulesResource($rules))->additional($metaData);

		} catch (\Exception $e) {
			return metaData(false, $request, 30044, '', 502, errorDesc($e), 'Error occured in server side ');
		}
	}




	public function destroy(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'id' => ['required', Rule::exists('maintenance_rules', 'id,deleted_at,NULL')->where('user_id', esoId())]], [
				'id.required' => 'Invalid ID.',
			]);
			if ($validator->fails()) {
				return $metaData = metaData('false', $request, '30045', '', '502', '', $validator->messages());
			}
			try {
			MaintenanceRule::find($request->id)->vehicleRuleService()->where('maintenance_rules_id', $request->id)->detach();
			$rules = MaintenanceRule::find($request->id);
			$rules->delete();
			
			$metaData=metaData(true, $request, '30045', 'success', 200, '');
            return  merge($metaData, ['data'=>['deleted_id'=>$request->id]]);
			
		} catch (\Exception $e) {
				return metaData(false, $request, 30045, '', 502, errorDesc($e), 'Error occured in server side ');
			}
	}


}
