<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemberAddress extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'member_addresses';
    protected $fillable = ['address_name', 'state_id', 'street_address', 'zipcode', 'location_type', 'facility_autofill', 'latitude', 'longitude', 'department','member_id'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_autofill_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department');
    }


    public function departmentDetails()
    {
        return $this->belongsTo(Department::class, 'department');
    }
}
