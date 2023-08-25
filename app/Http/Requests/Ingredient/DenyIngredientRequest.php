<?php

namespace App\Http\Requests\Ingredient;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DenyIngredientRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'ingredientid' => ['integer', 'required', 'exists:ingredients,id'],
            'deny'         => ['accepted', 'required'],
            'typeList'     => ['integer', 'required'],
            'denymessage'  => ['string', 'required', 'min:2'],
        ];
    }
}
