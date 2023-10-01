<?php

namespace App\Http\Requests\Recipe;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RecipeStoreRequest extends FormRequest
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
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'nom'                  => ['string', 'required', 'min:2'],
            'photoInput'           => ['nullable','mimes:jpg,png,jpeg,gif,svg,avif,webp'],
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
