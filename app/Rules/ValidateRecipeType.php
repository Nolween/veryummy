<?php

namespace App\Rules;

use App\Enums\RecipeTypes;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidateRecipeType implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value == 'all') {
            return true;
        }

        return in_array($value, RecipeTypes::allValues());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Le type de recette est invalide';
    }
}
