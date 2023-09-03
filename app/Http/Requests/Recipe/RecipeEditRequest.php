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
        $user = Auth::user();

        return $recipe && (($user->id === $recipe->user_id) || ($user->role === User::ROLE_ADMIN));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
