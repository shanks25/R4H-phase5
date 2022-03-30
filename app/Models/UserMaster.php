<?php

namespace App\Models;

use App\Traits\Timezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMaster extends Model
{
    use HasFactory,Timezone;

    protected $table='users';
    protected $guarded=[];
}
