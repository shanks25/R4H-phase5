<?php

namespace App\Models;

use App\Traits\LocalScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class MaintenanceRule extends Model
{
	use HasFactory, LocalScopes, SoftDeletes;
	
		protected $table = "maintenance_rules";
		protected $fillable = ['name', 'servicing_miles', 'notification_content', 'vehicle_id','user_id'];
	
	public function vehicle()
	{
		return $this->belongsTo('App\Models\Vehicle', 'vehicle_id');
	}

	public static function getVehicleCountExport($id)
	{
		return MaintenanceRule::select('id')->whereIN('id', $id)->get()->count();
	}
	public function vehicleRuleService()
    {
        return $this->belongsToMany(VehicleServiceMaster::class, 'vehicle_maintenance_rules_service','maintenance_rules_id','vehicle_service_id');
    }

	public static function getVehicleExport($start = 0, $id)
	{
		return MaintenanceRule::with(['vehicle'])->select('id','vehicle_id','created_at','name','servicing_miles','notification_content')->whereIN('id', $id)->orderBy("id", "DESC")->get()->toArray();
	}
}
