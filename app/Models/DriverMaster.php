<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\TripMaster;
use App\Traits\LocalScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverMaster extends Model
{
    use HasFactory,LocalScopes, SoftDeletes;

    protected $table = 'driver_master_ut';
    protected $fillable = ['first_name', 'middle_name', 'last_name', 'name', 'address', 'second_address', 'address_lng', 'address_lat', 'mobile_no', 'suffix', 'ssn', 'license_class', 'license_no', 'license_state', 'license_expiry', 'DOB', 'email', 'password', 'user_id'];

    public function driverLevelservices()
    {
        return $this->belongsToMany(MasterLevelOfService::class, 'driver_level_of_service','driver_id','level_of_service_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driverLeaveDetails()
    {
        return $this->hasMany(DriverLeaveDetail::class,'driver_id');
    }

    public function driverZones()
    {
        return $this->belongsToMany(ZoneMaster::class, 'driver_zone', 'driver_id', 'zone_id');
    }

    public function trips()
    {
        return $this->hasMany(TripMaster::class, 'driver_id');
    }
    public function zone()
    {
        return $this->hasMany(DriverZones::class, 'driver_id');
    }
    public function driverServiceRate()
    {
        return $this->hasMany(DriverServiceRate::class, 'driver_id');
    }

    public function step()
    {
        return $this->hasOne(DriverRequestStep::class, 'driver_id');
    }
    public function states()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function county()
    {
        return $this->belongsTo(CountyNames::class, 'county_id');
    }
    public function zipcode()
    {
        return $this->belongsTo(CountyMaster::class, 'city_id');
    }
    public function step5()
    {
        return $this->hasOne(DriverRequestStep5::class, 'driver_id');
    } 
    public function employees()
    {
        return $this->hasOne(Driverrequestemployees::class, 'driver_id');
    } 
    public function user()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }
    public function referencescontact()
    {
        return $this->hasOne(Driverrequestreferencescontact::class,'driver_id');
    }
    public function driverexam()
    {
        return $this->hasMany(DriverRequestFormFinalExamQuestionAnswer::class,'driver_id');
    }
    
    public static function filterDriver($request, $driver)
    {

        if ($request->filled('driver_type')) {

            $driver =  $driver->where('driver_type', $request->type);
        }
        if ($request->filled('status')) {
            $driver =  $driver->where('status', $request->status);
        }
        if ($request->filled('insurance_type')) {
            $driver =  $driver->where('insurance_type', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $driver = $driver->where(function ($driver) use ($search) {
                return   $driver->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('DOB', 'LIKE', "%{$search}%")
                    ->orWhere('mobile_no', 'LIKE', "%{$search}%")
                    ->orWhere('license_no', 'LIKE', "%{$search}%")
                    ->orWhere('license_state', 'LIKE', "%{$search}%")
                    ->orWhere('license_expiry', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('password', 'LIKE', "%{$search}%")
                    ->orWhere('timezone', 'LIKE', "%{$search}%")
                    ->orWhereHas('vehicle', function ($driver) use ($search) {
                        return     $driver->where('model_no', 'LIKE', "%{$search}%");
                    });
            });
        }
        return $driver;
    }
    public static function filterCompanayDriver($request, $driver)
    {
        $driver->whereHas('step', function($q) use($request){
			if ($request->overall_status != NULL) {
				$q->where('overall_status', $request->overall_status);
			}
			if ($request->step3_approval_status_filter != NULL) {
				$q->where('step3_approval_status', $request->step3_approval_status_filter);
			}
			if ($request->final_approval_status_filter != NULL) {
				$q->where('step6_approval_status', $request->final_approval_status_filter);
			} 
		})->
		select('*');
		$driver->where('full_registration_done', 1);

		$driver_type = $request->driver_type;
		if($driver_type == 'ISP')
		{
			$driver->where('driver_type', 'ISP');
		}
		else
		{
			$driver->where('driver_type', 'Company');
		}

		if ($request->filled('start_date')) {
			$date = Carbon::parse($request->start_date, auth()->user()->timezone)
			->startOfDay()
			->setTimezone(config('app.timezone'));
			$driver->where('created_at', '>=', $date);
		}

		if ($request->filled('end_date')) {
			$end_date = Carbon::parse($request->end_date, auth()->user()->timezone)
			->endOfDay()
			->setTimezone(config('app.timezone'));
			$driver->where('created_at', '<=', $end_date);
		}

		if ($request->filled('search')) {
			$search = $request->search;
			$driver->where(function ($q) use ($search) {
				$q->where('name', 'LIKE', '%' . $search . '%');
				//->orWhere('step3_approve_reject_status_remark', 'LIKE', '%' . $search . '%')
				//->orWhere('final_approve_reject_status_remark', 'LIKE', '%' . $search . '%')
				//->orWhere('note', 'LIKE', '%' . $search . '%');
			});
		}

        return $driver;
    }
}
