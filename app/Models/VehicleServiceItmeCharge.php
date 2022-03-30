<?php

namespace App\Models;
use App\Traits\LocalScopes;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleServiceItmeCharge extends Model
{
    use HasFactory, LocalScopes, SoftDeletes;

    protected $table = "vehicle_service_item_charge";
    protected $fillable = ['item', 'details', 'qty', 'amount', 'total','invoice_id'];
    





}
