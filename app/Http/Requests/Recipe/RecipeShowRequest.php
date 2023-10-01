<?php

namespace App\Http\Requests\Recipe;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RecipeShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $recipeId = $this->route('id');
        $recipe = Recipe::findOrFail($recipeId);

        $user = Auth::user();

        return $recipe->is_accepted || ($user && $user->role === User::ROLE_ADMIN);
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
