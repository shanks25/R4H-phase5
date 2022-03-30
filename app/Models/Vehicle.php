<?php

namespace App\Models;

use App\Traits\LocalScopes;
use App\Models\MasterLevelOfService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory, LocalScopes, SoftDeletes;

    protected $table = 'vehicle_master_ut';
    protected $fillable = ['type', 'Year', 'model_no', 'manufacturer', 'status', 'license_plate', 'odometer', 'documents', 'unit_no', 'CTS_no', 'user_id', 'miles_per_gallon', 'VIN', 'odometer_start_date', 'documents', 'registration_expiry_date', 'insurance_expiry_date'];


    public function masterLevelservices()
    {
        return $this->belongsToMany(MasterLevelOfService::class, 'vehicle_level_of_service', 'vehicle_id', 'level_of_service_id');
    }

    public static function filterVehicle($request, $vehicle)
    {

        if ($request->filled('type')) {

            $vehicle =  $vehicle->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $vehicle =  $vehicle->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $vehicle = $vehicle->where(function ($vehicle) use ($search) {
                return   $vehicle->where('type', 'LIKE', "%{$search}%")
                    ->orWhere('Year', 'LIKE', "%{$search}%")
                    ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                    ->orWhere('VIN', 'LIKE', "%{$search}%")
                    ->orWhere('unit_no', 'LIKE', "%{$search}%")
                    ->orWhere('CTS_no', 'LIKE', "%{$search}%")
                    ->orWhere('registration_expiry_date', 'LIKE', "%{$search}%")
                    ->orWhere('insurance_expiry_date', 'LIKE', "%{$search}%")
                    ->orWhere('license_plate', 'LIKE', "%{$search}%")
                    ->orWhere('odometer', 'LIKE', "%{$search}%")
                    ->orWhereHas('masterLevelservices', function ($vehicle) use ($search) {
                        return     $vehicle->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }
        return $vehicle;
    }
    public static function getOdometer($vehicleId)
    {
        $oldOdoMeter = OdoMeter::select('odometer')->where('vehicle_id', $vehicleId)->latest()->first();
        if ($oldOdoMeter) {
            return $oldOdoMeter->odometer;
        } else {
            return "0";
        }
    }
    public function driver()
    {
        return $this->hasOne(DriverMaster::class, 'vehicle_id');
    }
}
