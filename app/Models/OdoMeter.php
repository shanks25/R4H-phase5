<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OdoMeter extends Model
{
    use HasFactory;
    
    protected $table = 'odometers';
    protected $guarded = [];
    
}
