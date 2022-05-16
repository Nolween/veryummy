<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserMailExists implements Rule
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
        $userId = Auth::id();
        // Si l'utilisateur est connecté
        if ($userId) {
            return User::where('email', $value)->where('id', '!=', $userId)->doesntExist();
        }
        // Si l'utilisateur n'est pas connecté
        else {
            return User::where('email', $value)->doesntExist();
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Le mail indiqué existe déjà dans la base de données';
    }
}
