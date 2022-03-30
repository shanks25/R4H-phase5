<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class BlockFutureDatesRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $msg ;
    public function __construct($msg)
    {
        $this->msg = $msg ;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $request_date)
    {
        try {
            $today =   Carbon::now()->timezone(eso()->timezone);
        
            $request_date = Carbon::parse($request_date)->timezone(eso()->timezone);
            return  $today->gte($request_date);
        } catch (\Throwable $th) {
            return false ;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->msg ;
    }
}
