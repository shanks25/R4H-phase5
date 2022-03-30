<?php

namespace App\Models;

use App\Traits\LocalScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleMaintenance extends Model
{
    use HasFactory, LocalScopes, SoftDeletes;
    
    protected $table = "vehicle_maintenance_requests";

    //  protected $guarded = ['edit_make','upload_file','save','service_details'];
    protected $fillable = ['user_id', 'vehicle_id', 'other_details','other_service_details','request_date', 'driver_id', 'maintenance_type', 'garage_id', 'mileage', 'shop_name'];

    public function vehicleMaintenanceService()
    {
        return $this->belongsToMany(VehicleServiceMaster::class, 'vehicle_maintenance_request_service','vehicle_maintenance_requests_id','vehicle_service_id');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo('App\Models\DriverMaster', 'driver_id');
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\VehicleInvoice', 'maintenance_id');
    }


    public static function getVehicleCountExport($id)
    {
        return VehicleMaintenance::select('id')->whereIN('id', $id)->get()->count();
    }


    public static function getVehicleExport($start = 0, $id)
    {
        return VehicleMaintenance::with(['driver','vehicle'])->select('id', 'driver_id', 'vehicle_id', 'maintenance_request_id', 'vehicle_invoice_id', 'shop_name', 'shop_contact_number', 'work_completed_by', 'total_due')->whereIN('id', $id)->orderBy("id", "DESC")->get()->toArray();
    }

    public static function filterTicket($request,$ticket)
    {
        if ($request->filled('status')) {
            $ticket =$ticket->where('payor_type', $request->status);
        }
        if ($request->filled('ticket_id')) {
            $ticket =$ticket->where('ticket_id', $request->ticket_id);
        }
        return $ticket;
    }


}
