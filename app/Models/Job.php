<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Timezone;

class Job extends Model
{
    use HasFactory,Timezone;
    protected $table = "jobs_applications";
	protected $fillable = [];
}
