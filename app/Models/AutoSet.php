<?php

namespace App\Models;
use App\Traits\LocalScopes;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AutoSet extends Model
{
    use HasFactory, LocalScopes, SoftDeletes;

    protected $table = "auto_sets";
    protected $fillable = ['payor_type', 'payor_id', 'auto_set_time', 'payable_type', 'user_id'];
    

    public function payor()
    {
        return $this->morphTo(__FUNCTION__, 'payable_type', 'payor_id');
    }

    public function payorTypeNames()
    {
        return $this->belongsTo(PayorType::class, 'payor_type');
    }

    public static function filterAutoSet($request,$autoset)
    {
       

        if ($request->filled('payor_type')) {
 
            $autoset =$autoset->where('payor_type', $request->payor_type);
       }

     if ($request->filled('search')) {

        $search=$request->search;
      
           $autoset =$autoset->where('created_at', 'LIKE', "%{$search}%")
         ->orWhere('auto_set_time', 'LIKE', "%{$search}%") 
        
         ->orWhereHas('payorTypeNames',function ($q) use ( $search)  {
           return	 $q->where('name', 'LIKE', "%{$search}%");
        })
        ->orWhereHas('payor',function ($q) use ( $search)  {
           return	 $q->where('name', 'LIKE', "%{$search}%");
        });  
    
       
    }
   return $autoset;
    }

}
