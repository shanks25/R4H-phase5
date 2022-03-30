<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Timezone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory,Timezone,SoftDeletes;

    protected $table = 'complaint_driver';
    protected $fillable =['date','type','description','upload','driver_id','user_id'];


    public static function filtercomplaintList($request,$query)
    {
    if (@$request['search']) {
        $search=$request['search'];
        $query->where(function ($q) use ($search) {
            $q->where('created_at', 'LIKE', '%' . $search . '%')
                ->orWhere('type', 'LIKE', '%' . $search . '%')
                ->orWhere('description', 'LIKE', '%' . $search . '%')
                ->orWhere('driver_id',  $search );
        });
        
    }
    if (@$request['date']) {
        $date=$request['date'];
        $date = Carbon::parse($date, eso()->timezone)
        ->startOfDay()
        ->setTimezone(config('app.timezone'));
        // print_r($date);die;

    $query->where('created_at', '>=', $date);
        
    }
   return $query;
    }
}
