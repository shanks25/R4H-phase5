<?php

namespace App\Models;

use Carbon\Carbon;

use App\Traits\Timezone;
use App\Traits\LocalScopes;
use App\Models\PreviousEmployeer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Clerical extends Model
{
    use HasFactory,Timezone, SoftDeletes;

    protected $table = "clerical_form";
	protected $dates = [
        'rejected_datetime','rejected_datetime','last_status_updated_at','final_approval_date'
    ];
	protected $fillable =['auth_sign','felony_sign','_token','employer','job_title','supervisor','start_date','salary','job_description','reason_of_leaving','may_we_contact','end_date','employer_telephone','signed_image','image1','image2','nationl_image','sam_image','oig_image','note','eligible_proof','is_step3_approved','home_telephone','address','job_id','step1_status','step2_status','step3_status','step4Point1_status','step1_point3_status','step4Point3_status','step3_approve_reject_status_remark','overall_status','first_name','middle_name','last_name','final_approve_reject_status_remark','full_name','is_final_approved','is_archived','step','apply_for','date','ssn','city','state','zipcode','other_phone','eighteen_plus','date_of_available','how_find_position','any_relative','	please_list','position_apply_for','ever_worked','worked_dates','prior_position','leaving_reason','cpr_certificate_number','cpr_expiry_date','cpr_agency','cdl_certificate_number','cdl_expiry_date','cdl_agency','defensive_certificate_number','defensive_expiry_date','defensive_agency','other_certificate_number','other_expiry_date','other_agency','valid_driving_license','class','issued_state','driving_license','moving_violation','ever_conviced','ever_conviced_explain','ever_excluded','ever_excluded_yes','gap_in_employment','disciplined','probation','fired_violation','fired_assault','fired_harassment','fired_patient','fired_alcohol','charged_dui','past_explain','school_name','school_address','school_years','school_graduate','school_highest_grade','ged','college_name','college_address','college_years','college_graduate','college_highest_grade','degree','major','tech_name','tech_address','tech_year','tech_graduate','tech_highest_grade','tech_certificate','tech_license','tech_expires','other_college_name','other_college_address','other_college_years','other_college_graduate','other_college_higesht_grade','other_college_degree','other_college_major','other_school_name','other_school_address','other_school_years','other_school_graduate','other_school_higest_grade','other_school_certificate','other_school_license','other_school_expires','other_school_expires2','other_school_other','ems_training','ems_professional','additional_qualification','app_date','signed','sign_type','via_email','auth_name','auth_print_name','auth_date','auth_ss','auth_dob','auth_sign_img','felonies_name','felonies_print_name','felonies_date','felonies_ss','felonies_dob','felony_sign_img','military_branch','military_began','military_ended','military_rank','military_discharged','military_location','document1','document1_image','document2','document2_image','step3By','oig_doc','oig_expiry','sam_doc','sam_expiry','nationl_doc','nationl_expiry','step1_point2_status','mouse_sign','step2_doc1_expiry','step2_doc2_expiry','step4_new_date','	step4_revised_date','employee_name','step4_address','step4_phone_number','step4_cell_number','step4_dob','step4_ssn','part_time','full_time','position','wage','company_employment','contractor_employment','emergency_name','emergency_phone','emergency_address','shirt_size','date1','date2','date3','date4','date5','date6','date7','date8','date9','date10','date11','step4_name1','step4_name2','step4_name3','step4_name4','step4_name5','step4_name6','step4_name7','step4_name8','made_this','day_of','made_this2','day_of2','personnel','step4Point2_status','step4Point3_status'];
    

    public function previousEmployeers()
	{
		return $this->hasMany(PreviousEmployeer::class, 'clerical_id');
	}


	public function job()
	{
		return $this->belongsTo('App\Models\Job', 'job_id');
	}

	public function eso()
	{
		return $this->belongsTo('App\Models\User', 'user_id');
	}


	
	public function getFullNameAttribute()
	{
		return $this->first_name.' '.$this->middle_name.' '.$this->last_name;
	}

	public function scopeArchived($query)
	{
		return $query->where('is_archived',1);
	}

	public function scopeInProgress($query)
	{
		return $query->where('is_archived',0);
		// return $query->where('is_final_approved',2)->orWhere('created_at', '>=', Carbon::now()->subDays(90)->toDateTimeString());
	}

	public function scopeRejected($query)
	{
		return $query->where('is_final_approved',3)->orWhere('is_step3_approved',3);
	}

	public function scopeActive($query)
	{
		return $query->where(function ($q) {
			$q->where('is_final_approved','!=', 3)
			->where('is_step3_approved','!=', 3);
		})->where(function ($q) {
			$q->where('is_final_approved', 1)
			->orwhere('is_step3_approved', 1);
		});

	}

	public function scopeApproved($query)
	{
		return $query->where('is_final_approved',2);
	}

	public function isRejected()
	{
		if ($this->is_final_approved == 3  || $this->is_step3_approved == 3) {
			return true; 
		}

		return false ;
	}


	public static function filterclericalList($request,$query)
    {
    if (@$request['search']) {
        $search=$request['search'];
        $query->where(function ($q) use ($search) {
            $q->where('created_at', 'LIKE', '%' . $search . '%')
                ->orWhere('last_status_updated_at', 'LIKE', '%' . $search . '%')
                ->orWhere('via_email', 'LIKE', '%' . $search . '%')
                ->orWhere('address', 'LIKE', '%' . $search . '%')
                ->orWhere('is_step3_approved', 'LIKE', '%' . $search . '%');
        });
        
    }
	
	if (@$request['overall_status']) {
        $overall_status=$request['overall_status'];
        $query->where(function ($q) use ($overall_status) {
            $q->where('overall_status', 'LIKE', '%' . $overall_status . '%'); 
        });
        
    }
	if (@$request['is_final_approved']) {
        $is_final_approved=$request['is_final_approved'];
        $query->where(function ($q) use ($is_final_approved) {
            $q->where('overall_status', 'LIKE', '%' . $is_final_approved . '%');
        });
        
    }
	if (@$request['is_step3_approved']) {
        $is_step3_approved=$request['is_step3_approved'];
        $query->where(function ($q) use ($is_step3_approved) {
            $q->where('overall_status', 'LIKE', '%' . $is_step3_approved . '%');
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
