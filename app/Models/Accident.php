<?php

namespace App\Models;

use App\Models\AccidentPassenger;
use App\Models\DriverMaster;
use App\Models\Vehicle;
use App\Traits\LocalScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Accident extends Model
{
    use HasFactory,LocalScopes, SoftDeletes;

    protected $table = "accidents";

    protected $fillable =[
        'date' ,
        'trip_id' ,
        'time' 	,
        'weather' ,
        'location_of_accident' ,
        'accident_details' ,
        'your_towing_company' ,
        'your_towing_company_phone' ,
        'other_towing_company' ,
        'other_towing_company_phone' ,
        'owner_name' ,
        'owner_address' ,
        'owner_phone' ,
        'other_vehicle_model' ,
        'other_vehicle_year' ,
        'other_vehicle_color' ,
        'other_license_plate' ,
        'other_insurance_company' ,
        'other_agent_name' ,
        'other_agent_phone' ,
        'other_driver_name' ,
        'other_driver_address' ,
        'other_driver_phone' ,
        'police_officer_name' ,
        'police_officer_phone' ,
        'police_department' ,
        'police_badge' ,
        'police_other_info' ,
        'witness_name1' ,
        'witness_address1' ,
        'witness_home_phone1' ,
        'witness_work_phone1' ,
        'witness_name2' ,
        'witness_address2' ,
        'witness_home_phone2' ,
        'witness_work_phone2' ,
        'sketch' ,
        'driver_id',
        'user_id',
        'timezone',
        'video',
        'image',
        'other_vehicle_make',
        'added_by',
        'vehicle_id',
        'vehicle_name',
        'other_insurance_card_image',
        'other_license_image',
        'other_license_plate_image',
        'accident_image',
        'your_vehicle_passengers',
        'your_vehicle_passengers_injuries',
        'other_vehicle_passengers',
        'other_vehicle_passengers_injuries',
        'insurance_policy_number'
    ];

    public function driver()
    {
        return $this->belongsTo(DriverMaster::class, 'driver_id');
    }


    public static function getVehicleCountExport($id)
    {
        return Accident::select('id')->whereIN('id', $id)->get()->count();
    }


    public static function getVehicleExport($start = 0, $id)
    {
        return Accident::with('driver')->select('id', 'created_at', 'date', 'time', 'driver_id', 'vehicle_name')->whereIN('id', $id)->orderBy("id", "DESC")->get()->toArray();
    }

    public function yourPassengers()
    {
        return $this->hasMany(AccidentPassenger::class, 'accident_id')->where('type', 1);
    }

    public function otherPassengers()
    {
        return $this->hasMany(AccidentPassenger::class, 'accident_id')->where('type', 2);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    
    
    
    public static function filterAccident($request,$accident)
    {
        
        if ($request->filled('vehicle_id')) {
           
            $accident =  $accident->where('vehicle_id',$request->vehicle_id);
        }
        if ($request->filled('driver_id')) {
            $accident =  $accident->where('driver_id',$request->driver_id );
        }

        if ($request->filled('search')) {
            $search=$request->search;
               $accident =$accident->where(function ($accident) use ( $search)  {
                return   $accident->where('trip_id', 'LIKE', "%{$search}%")
                            ->orWhere('driver_id', 'LIKE', "%{$search}%")
                            ->orWhere('vehicle_name', 'LIKE', "%{$search}%")
                            ->orWhere('vehicle_id', 'LIKE', "%{$search}%")
                            ->orWhere('added_by', 'LIKE', "%{$search}%")
                            ->orWhere('timezone', 'LIKE', "%{$search}%")
                            ->orWhere('date', 'LIKE', "%{$search}%")
                            ->orWhere('time', 'LIKE', "%{$search}%")
                            ->orWhere('day', 'LIKE', "%{$search}%")
                            ->orWhere('weather', 'LIKE', "%{$search}%")
                            ->orWhere('location_of_accident', 'LIKE', "%{$search}%")
                            ->orWhere('accident_details', 'LIKE', "%{$search}%")
                            ->orWhere('your_towing_company_phone', 'LIKE', "%{$search}%")
                            ->orWhere('other_towing_company', 'LIKE', "%{$search}%")
                            ->orWhere('other_towing_company_phone', 'LIKE', "%{$search}%")
                            ->orWhere('owner_name', 'LIKE', "%{$search}%")
                            ->orWhere('owner_address', 'LIKE', "%{$search}%")
                            ->orWhere('owner_phone', 'LIKE', "%{$search}%")
                            ->orWhere('other_vehicle_make', 'LIKE', "%{$search}%")
                            ->orWhere('other_vehicle_model', 'LIKE', "%{$search}%")
                            ->orWhere('other_vehicle_year', 'LIKE', "%{$search}%")
                            ->orWhere('other_vehicle_color', 'LIKE', "%{$search}%")
                            ->orWhere('other_license_plate', 'LIKE', "%{$search}%")
                            ->orWhere('other_insurance_company', 'LIKE', "%{$search}%")
                            ->orWhere('other_agent_name', 'LIKE', "%{$search}%")
                            ->orWhere('other_agent_phone', 'LIKE', "%{$search}%")
                            ->orWhere('other_driver_name', 'LIKE', "%{$search}%")
                            ->orWhere('other_driver_address', 'LIKE', "%{$search}%")
                            ->orWhere('other_driver_phone', 'LIKE', "%{$search}%")
                            ->orWhere('police_officer_name', 'LIKE', "%{$search}%")
                            ->orWhere('police_officer_phone', 'LIKE', "%{$search}%")
                            ->orWhere('police_department', 'LIKE', "%{$search}%")
                            ->orWhere('police_badge', 'LIKE', "%{$search}%")
                            ->orWhere('police_other_info', 'LIKE', "%{$search}%")
                            ->orWhere('witness_name1', 'LIKE', "%{$search}%")
                            ->orWhere('witness_address1', 'LIKE', "%{$search}%")
                            ->orWhere('witness_home_phone1', 'LIKE', "%{$search}%")
                            ->orWhere('witness_work_phone1', 'LIKE', "%{$search}%")
                            ->orWhere('witness_name2', 'LIKE', "%{$search}%")
                            ->orWhere('witness_home_phone2', 'LIKE', "%{$search}%")
                            ->orWhere('witness_work_phone2', 'LIKE', "%{$search}%")
                            ->orWhere('your_vehicle_passengers', 'LIKE', "%{$search}%")
                            ->orWhereHas('vehicle',function ($accident) use ( $search)  {
                                return	 $accident->where('vehicle_name', 'LIKE', "%{$search}%");
                             });
            });
        }

    
       return $accident;
    }

}
