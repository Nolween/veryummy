<?php

namespace App\Http\Requests\Recipe;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RecipeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $recipe = Recipe::findOrFail($this->get('recipeid'));
        $user = Auth::user();

        if($user->is_banned) {
            return false;
        }

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
            'recipeid'             => ['integer', 'required', 'exists:recipes,id'],
            'nom'                  => ['string', 'required', 'min:2'],
            'photoInput'           => 'nullable|mimes:jpg,png,jpeg,gif,svg,avif,webp',
            'preparation'          => ['integer', 'required', 'min:0', 'max:1000'],
            'cuisson'              => ['integer', 'nullable', 'min:0', 'max:1000'],
            'parts'                => ['integer', 'required', 'min:0', 'max:1000'],
            'stepCount'            => ['integer', 'nullable'],
            'type'                 => ['integer', 'exists:recipe_types,id', 'required'],
            'ingredientCount'      => ['integer', 'nullable'],
            '*.ingredientId'       => ['integer', 'exists:ingredients,id', 'nullable'],
            '*.ingredientName'     => ['string', 'nullable'],
            '*.ingredientUnit'     => ['numeric', 'exists:units,id', 'nullable'],
            '*.ingredientQuantity' => ['numeric', 'nullable'],
            '*.stepDescription'    => ['string', 'nullable'],
        ];
    }
}
