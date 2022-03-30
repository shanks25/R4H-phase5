<?php

namespace App\Models;

use App\Traits\LocalScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Timezone;

class Garage extends Model
{
    use HasFactory,Timezone;
    protected $fillable = [
        'name', 'email', 'user_id',
    ];
}
