<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverRequestStep extends Model
{
    use SoftDeletes;
    protected $table = "driver_request_form_steps";
    protected $guarded = [
		
	];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
