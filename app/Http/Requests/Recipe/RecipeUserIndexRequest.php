<?php

namespace App\Http\Requests\Recipe;

use App\Rules\DietExists;
use App\Rules\ValidateRecipeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RecipeUserIndexRequest extends FormRequest
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
     * @return array<string, array<ValidateRecipeType|DietExists|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'nullable'],
            'type' => ['string', 'nullable', new ValidateRecipeType],
            'diet' => ['string', 'nullable', new DietExists],
        ];
    }
}
