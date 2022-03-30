<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LocalScopes;
use App\Traits\Timezone;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ProviderMaster extends Model
{
    use HasFactory,LocalScopes;
    use Timezone, SoftDeletes;
    protected $table = 'provider_master';

    protected $fillable = ['name','provider_name','provider_fullname','email','phone_number','contact_person','contact_phone_number','template','city','address','zipcode','is_required','addtional_info','buffer_minutes','suit','fax','website','phone_ext','payor_status','credit','balance','active_payer','discount_percent','discount_dolar','periodicity','invoice_start_date','invoice_finish_date','deactivate_reason','signature','odometer','signature_at_pu','signature_at_do','calculate_short_distance','calculate_long_distance','	calculate_efficent_distance','show_price_setting','round_price_setting','round_miles_setting','private_pay','require_auth','multiload','provider_id','provider_tax_id','company_name','company_address','company_phone','county_required','mobile_name','discount_use_percent','pay_days','is_reflect','same_price','same_price_kind','voucher_balance','diagnosis_code','group_legs','group_legs_count','auto_approve','noshow_rate','adjuster','allow_delay_pickup','allow_delay_dropoff','will_call_orders','edi_account_id','edi_account_name','timezone','need_accept','show_past_trips','account_portal_pricing','additional','is_contract','name_city','user_id'

    ];
    
    public function getNameCityAttribute()
    {
        return "{$this->name} {$this->city}";
    }
    
    public static function filterBrokerList($request, $query)
    {
        if (@$request['search']) {
            $search=$request['search'];
            $query->where(function ($q) use ($search) {
                $q->where('created_at', 'LIKE', '%' . $search . '%')
                ->orWhere('city', 'LIKE', '%' . $search . '%')
                ->orWhere('name_city', 'LIKE', '%' . $search . '%')
                ->orWhere('provider_name', 'LIKE', '%' . $search . '%')
                ->orWhere('phone_number', 'LIKE', '%' . $search . '%')
                ->orWhere('state', 'LIKE', '%' . $search . '%');
            });
        }
        if (@$request['start_date']) {
            $start_date=$request['start_date'];
            $date = Carbon::parse($start_date, eso()->timezone)
        ->startOfDay()
        ->setTimezone(config('app.timezone'));
            // print_r($date);die;

            $query->where('created_at', '>=', $date);
        }
        if (@$request['end_date']) {
            $end_date=$request['end_date'];
            $end_date = Carbon::parse($end_date, eso()->timezone)
                ->endOfDay()
                ->setTimezone(config('app.timezone'));
            $query->where('created_at', '<=', $end_date);
        }
        return $query;
    }
}
