<?php

namespace App\Models;

use App\Traits\LocalScopes;
use App\Scopes\FacilityScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InsurancePeriodLog extends Model
{
    use HasFactory,LocalScopes;
    protected $table = "insurance_period_log"; 

    

 
}
