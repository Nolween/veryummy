<?php

namespace App\Http\Requests\Ingredient;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AllowIngredientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('access-admin-role');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'ingredientid' => ['integer', 'required', 'exists:ingredients,id'],
            'allow'        => ['accepted', 'required'],
            'finalname'    => ['string', 'required', 'min:2', 'max:255', 'unique:ingredients,name,'.$this->ingredientid],
            'typeList'     => ['integer', 'required'],
            'vegetarian'   => ['boolean', 'nullable'],
            'vegan'        => ['boolean', 'nullable'],
            'glutenfree'   => ['boolean', 'nullable'],
            'halal'        => ['boolean', 'nullable'],
            'kosher'       => ['boolean', 'nullable'],
        ];
    }
}
