<?php

namespace App\Models;

use App\Traits\LocalScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BaseLocation extends Model
{
    use HasFactory ,LocalScopes,SoftDeletes;

    protected $table='base_location_master';
    protected $fillable = ['state', 'city_id', 'zipcode', 'address', 'user_id','name','is_default_location','detail_address'] ;
    

    public static function filterBaselocation($request,$baseLocation)
    {
        
        
        if ($request->filled('start_date')) {
             $baseLocation->where('base_location_master.created_at','>=',start_date($request->start_date) );
        }
        if ($request->filled('end_date')) {
            $baseLocation->where('base_location_master.created_at','<=',end_date($request->end_date) );
        }

        if ($request->filled('search')) {
            $search=$request->search;
               $baseLocation->where(function ($q) use ( $search)  {
                return   $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('address', 'LIKE', "%{$search}%")
                            ->orWhere('zipcode', 'LIKE', "%{$search}%");
            });
        }

       
        return $baseLocation;
       
    }
}
