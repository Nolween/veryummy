<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserNameExists implements Rule
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
            return User::where('name', $value)->where('id', '!=', $userId)->doesntExist();
        }
        // Si l'utilisateur n'est pas connecté
        else {
            return User::where('name', $value)->doesntExist();
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Le pseudo indiqué existe déjà dans la base de données';
    }
}
