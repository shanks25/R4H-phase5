<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Timezone;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleServiceMaster extends Model
{
    use HasFactory, Timezone,SoftDeletes;
    protected $table = "vehicle_service_master";


    

    public function vehicleMaintenanceRequests()
    {
        return   $this->belongsToMany(VehicleMaintenance::class, 'vehicle_maintenance_request_service', 'vehicle_service_id', 'vehicle_maintenance_requests_id');
    }
}
