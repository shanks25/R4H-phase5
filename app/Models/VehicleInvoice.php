<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Timezone;

class VehicleInvoice extends Model
{
    use HasFactory,Timezone;
    protected $table = "vehicle_invoice_details";
}
