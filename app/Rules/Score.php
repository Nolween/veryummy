<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Score implements Rule
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
        $value = (float) $value;

        return is_float($value) && $value >= 1 && $value <= 5 && fmod($value, 0.5) == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'La note doit être entre 1 et 5.';
    }
}
