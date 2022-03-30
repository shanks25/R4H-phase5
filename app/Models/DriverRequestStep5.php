<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverRequestStep5 extends Model
{
    use SoftDeletes;
    protected $table = "driver_request_form_steps_5";
    protected $guarded = [
		
	];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
