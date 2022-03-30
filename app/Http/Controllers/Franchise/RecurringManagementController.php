<?php

namespace App\Http\Controllers\Franchise;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\TripRecurringMaster;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TripRecurringResource;

class RecurringManagementController extends Controller
{
    public function index(Request $request)
    {
        try {
            $recurring = (new TripRecurringMaster)->newQuery();
            $recurring->with('trips:id,trip_no,recurring_master_id,date_of_service,appointment_time,shedule_pickup_time,leg_no');
            if ($request->filled('start_date')) {
                $recurring->whereDate('start_date', '>=', $request->start_date);
            }
    
            if ($request->filled('end_date')) {
                $recurring->whereDate('end_date', '<=', $request->end_date);
            }
            $recurring = $recurring->eso()->latest()->paginate(config('Settings.pagination'));
            return   TripRecurringResource::collection($recurring);
        } catch (\Exception $e) {
            return metaData(false, $request, 30007, '', 502, errorDesc($e), 'Error occured in server side ');
        }
    }

    public function show(Request $request)
    {
        return  ;
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recurring_id' => ['required','numeric', Rule::exists('trip_recurring_master', 'id')->where('user_id', esoId())->whereNull('deleted_at')],
        ], );
        
        if ($validator->fails()) {
            return metaData('false', $request, '3011', '', '502', '', $validator->messages());
        }
        $recurring = TripRecurringMaster::find($request->recurring_id)  ;
        $recurring->IncompleteTrips()->delete();
        $recurring->delete();

        $metaData=metaData(true, $request, '3011', 'success', 200, '');
        return  merge($metaData, ['data'=>['deleted_id'=>$request->recurring_id]]);
    }
}
