<?php

namespace App\Rules;

use Facades\App\Repository\TripReccRepo;
use Illuminate\Contracts\Validation\Rule;

class RecurringTripCountRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $trip_dates = TripReccRepo::getTripDates();
            return $trip_dates->count()  ;
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
        return 'no trips can be created for the selected range';
    }
}
