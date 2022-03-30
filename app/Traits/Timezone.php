<?php 

namespace App\Traits;

use Carbon\Carbon;

trait Timezone 
{

	public function getCreatedAtAttribute($date)
	{  

		$n = Carbon::parse($date,config('timezone'));

		if ($user = eso()) {
			return $n->setTimezone($user->timezone);
		}

		return $date;
	}

	public function getUpdatedAtAttribute($date)
	{   
		$n = Carbon::parse($date,config('timezone'));

		if ($user = eso()) {
			return $n->setTimezone($user->timezone);
		} 

		return $date;
	}
}