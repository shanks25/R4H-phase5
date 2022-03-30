<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driverrequestemployees extends Model
{
    use SoftDeletes;
    protected $table = "driver_request_employees";
    protected $guarded = [
		
	];
    public $timestamps = false;
}
