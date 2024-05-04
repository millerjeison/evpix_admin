<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AfterNow implements Rule
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
        $inputTimestamp = strtotime($value);
        $currentTimestamp = now()->timestamp;

        return $inputTimestamp > $currentTimestamp;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a date and time after the current time.';
    }
}
