<?php

namespace App\Models;

use App\Model\InvoiceItem;
use App\Model\ProviderMaster;
use App\Traits\Timezone;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Driverleavedetails extends Model
{
	use  Timezone;
	protected $table = "driver_leave_details";
	
}
