<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driverrequestreferencescontact extends Model
{
    use SoftDeletes;
    protected $table = "driver_request_references_contact";
    protected $guarded = [
		
	];
    public $timestamps = false;
}
