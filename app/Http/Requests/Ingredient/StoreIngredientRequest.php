<?php

namespace App\Http\Requests\Ingredient;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreIngredientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('authorized-user');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'ingredient' => ['string', 'required', 'min:2', 'max:255'],
            'rulescheck' => ['accepted', 'required'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ingredient.min'      => "L'ingrédient doit faire au moins 2 caractères",
            'ingredient.required' => "Veuillez donner un nom à l'ingrédient",
            'rulescheck.accepted' => 'Veuillez accepter les règles pour valider la proposition',
            'rulescheck.required' => 'Veuillez accepter les règles pour valider la proposition',
        ];
    }

}
