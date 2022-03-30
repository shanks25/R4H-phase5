<?php

namespace App\Models;
use App\Traits\LocalScopes;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleServiceInvoice extends Model
{
    use HasFactory, LocalScopes, SoftDeletes;

    protected $table = "vehicle_service_invoices";
    protected $fillable = ['invoice_no', 'ticket_id','service_request_id', 'service_date', 'odometter_upon_service','purchase_order', 'warranty_information', 'spacial_instructions', 'tax', 'sub_total', 'total', 'user_id'];
    
   public function ServiceItemCharge()
   {
       return $this->hasMany(VehicleServiceItmeCharge::class, 'invoice_id')->orderBy('item', 'ASC');
   }
    
}
