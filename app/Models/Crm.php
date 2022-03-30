<?php

namespace App\Models;

use App\Models\Department;
use App\Traits\LocalScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Model\PayorType;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Crm extends Model
{
    use HasFactory, SoftDeletes, LocalScopes;

    protected $table='crm';
    protected $fillable = ['user_id','type','name','name_city','city','street_address','address_type','state_id','zipcode','lat','lng','crm_mobile_no','representative','rep_mobile_no','email','addtional','bank','status','county','is_required','is_contract'];

    // public function setNameCityAttribute()
    // {
    //     return $this->attributes['name_city'] = $this->name."_".$this->city;
    // }

    public function departments()
    {
        return $this->hasMany(Department::class, 'crm_id');
    }

    public function getAddressAttribute()
    {
        return $this->street_address ;
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($facility) {
            $facility->departments()->each(function ($department) {
                $department->delete();
            });
        });
    }

    public function payorType()
    {
        return $this->belongsTo(PayorType::class, 'type');
    }

    public function user()
    {
        return $this->hasMany(Crm::class, 'user_id');
    }

    public static function filterCrmList($request, $crmList)
    {
        if (@$request['search']) {
            $search=$request['search'];
            $crmList = $crmList->where('name', 'LIKE', "%{$search}%")
        ->orWhere('name_city', 'LIKE', "%{$search}%");
        }
        if (@$request['start_date']) {
            $start_date=$request['start_date'];
            $crmList = $crmList->where('created_at', '>=', $start_date);
        }
        if (@$request['end_date']) {
            $end_date=$request['end_date'];
            $crmList = $crmList->where('created_at', '<=', $end_date);
        }
        return $crmList;
    }
}
