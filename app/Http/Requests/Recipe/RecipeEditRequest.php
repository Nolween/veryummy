<?php

namespace App\Http\Requests\Recipe;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RecipeEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $recipe = Recipe::findOrFail($this->route('id'));
        $user   = Auth::user();

        if ($user === null || $user->is_banned === 1) {
            return false;
        }

        return ($user->id === $recipe->user_id) || ($user->role === User::ROLE_ADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
