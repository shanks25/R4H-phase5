<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripImport extends Model
{
    use HasFactory;
    protected $table = 'trip_import_ut';
    protected $guarded = [];
}
