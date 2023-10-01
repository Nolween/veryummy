<?php

namespace App\Rules;

use App\Enums\Units;
use Illuminate\Contracts\Validation\Rule;

class UnitExists implements Rule
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
        $unitsArray = Units::allValues();
        return in_array($value, $unitsArray);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "L'unité n'existe pas";
    }
}
