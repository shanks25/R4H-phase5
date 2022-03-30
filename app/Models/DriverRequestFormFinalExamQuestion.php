<?php

namespace App\Models;

use App\Traits\Timezone;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\DriverExameCollection;

class DriverRequestFormFinalExamQuestion extends Model
{
	protected $table = "driver_request_form_final_exam_question";
	protected $guarded = [];

	public function queoptions()
	{
		return $this->hasMany(DriverRequestFormFinalExamQuestionOptions::class,'question_id');
	}

	public function queanswer()
	{
		return $this->hasOne(DriverRequestFormFinalExamQuestionAnswer::class, 'question_id');
	}


	public function questionlist($driver_id)
	{
		$driver_id=367;
		$qus=DriverRequestFormFinalExamQuestion::select('id','question')->with('queoptions:id,question_option,question_id') 
		->with(['queanswer' => function ($query) use ($driver_id) {
            $query->where('driver_id', $driver_id);
            $query->select('question_id', 'question_answer');
        }])->get();
        
		return $qus;// (new DriverExameCollection($qus));
	}
}
