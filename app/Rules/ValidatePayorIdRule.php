<?php

namespace App\Rules;

use App\Models\Crm;
use App\Models\Member;
use App\Models\Facility;
use App\Models\ProviderMaster;
use Illuminate\Contracts\Validation\Rule;

class ValidatePayorIdRule implements Rule
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
            if (request('payor_type') == 1) {
                $payor = Member::eso()->where('id', $value)->first();
            } elseif (request('payor_type') == 3) {
                $payor = ProviderMaster::eso()->where('id', $value)->first();
            } else {
                $payor = Crm::eso()->where('id', $value)->first();
            }

            if ($payor) {
                return true ;
            }

            return false ;
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
        return 'Invalid Payor Id';
    }
}
