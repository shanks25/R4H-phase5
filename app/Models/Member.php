<?php

namespace App\Models;

use App\Models\Payor;
use App\Models\State;
use App\Models\Category;
use App\Models\Facility;
use App\Traits\Timezone;
use App\Models\Department;
use App\Models\TripMaster;
use App\Traits\LocalScopes;
use Illuminate\Http\Request;
use App\Models\MemberAddress;
use App\Models\LevelofService;
use App\Models\ProviderMaster;
use App\Models\MasterLevelOfService;  
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory ,Timezone, SoftDeletes ,LocalScopes;
	protected $table='members_master';
	protected $guarded = [];

    public function addresses()
	{
		return $this->hasMany(MemberAddress::class, 'member_id');
	}

	public function trips()
	{
		return $this->hasMany(TripMaster::class, 'member_id');
	}

	public function tripsSelected()
	{
		return $this->hasMany(TripMaster::class, 'member_id')->select('id','member_id');
	}
 
	public function address()
	{
		return $this->hasOne(MemberAddress::class, 'member_id')->latest();
	}

	public function masterLevelOfService()
	{
		return $this->belongsTo(MasterLevelOfService::class, 'mode_of_transport');
	}

	public function primaryPayor()
    {
        return $this->morphTo(__FUNCTION__, 'payable_type', 'primary_payor_id');
    }


	public function primaryfacility()
	{
		return $this->belongsTo(Facility::class, 'primary_payor_id');
	}

	public function secondaryfacility()
	{
		return $this->belongsTo(Facility::class, 'secondary_payor_id');
	}

	public function primaryprovider()
	{
		return $this->belongsTo(ProviderMaster::class, 'primary_payor_id');
	}

	public function secondaryprovider()
	{
		return $this->belongsTo(ProviderMaster::class, 'secondary_payor_id');
	}

	  public function payor()
    {
    	return $this->belongsTo(Payor::class, 'payor_id');
    }

    public function primaryDepartment()
    {
    	return $this->belongsTo(Department::class, 'primary_payor_department');
    }

    public function secondaryDepartment()
    {
    	return $this->belongsTo(Department::class, 'secondary_payor_department');
    }

    public function categories()
    {
    	return $this->belongsToMany(Category::class,'member_category','member_id','category_id');
	}
	

		public static function filterMember($request, $members)
		{
			

			if ($request->filled('start_date')) {
				
				$members->where('created_at', '>=', start_date($request->start_date));
			}
			
	
			if ($request->filled('end_date')) {
				
				$members->where('created_at', '<=', end_date($request->end_date));
			}
	
				
			if ($request->filled('lavel_of_service')) {
				
				$members->where('mode_of_transport', $request->mode_of_transport);
			}
			if ($request->filled('state')) {
				$members->whereHas('address', function ($q) use ($request) {
					return $q->where('state_id', $request->state);
				});
			}
			
			if ($request->filled('dob')) {
				$members->whereYear('dob', $request->dob);
			}
	
			if ($request->filled('search')) {
				$search = $request->search;
	
				$members->where(function ($q) use ($search) {
					$q
						->Where('name', 'LIKE', '%' . $search . '%')
						->orWhere('mode_of_transport', 'LIKE', '%' . $search . '%')
						->orWhere('mobile_no', 'LIKE', '%' . $search . '%')
						->orWhereHas('address', function ($query) use ($search) {
							return	$query->where('street_address', 'like', '%' . $search . '%');
						});
				});
			}

			return $members;

		}
}
