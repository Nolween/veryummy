<?php

namespace App\Http\Requests\Recipe;

use App\Rules\DietExists;
use Illuminate\Foundation\Http\FormRequest;

class RecipeExplorationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<DietExists|string>>
     */
    public function rules(): array
    {
        return [
            'name'   => ['string', 'nullable'],
            'typeId' => ['integer', 'nullable', 'exists:recipe_types,id'],
            'diet'   => ['integer', 'nullable', new DietExists],
        ];
    }
}
