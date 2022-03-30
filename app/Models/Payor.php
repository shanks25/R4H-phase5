<?php

namespace App\Models;

use App\Traits\Timezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payor extends Model
{
	use Timezone, SoftDeletes;
	protected $table = "provider_master";

}
