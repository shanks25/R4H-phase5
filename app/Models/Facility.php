<?php

namespace App\Models;

use App\Traits\LocalScopes;
use App\Scopes\FacilityScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Facility extends Model
{
    use HasFactory,LocalScopes;
    protected $table = "crm";

    protected static function booted()
    {
        static::addGlobalScope(new FacilityScope);
    }

    public function getAddressAttribute()
    {
        return $this->street_address ;
    }
}
