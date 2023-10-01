<?php

namespace App\Http\Requests\Recipe;

use App\Rules\Score;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RecipeCommentRequest extends FormRequest
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
     * @return array<string, array<Score|string>>
     */
    public function rules(): array
    {
        return [
            // Le socre doit passer la règle Score de App/Rules/Score
            'score' => [new Score, 'required', 'max:5', 'min:1'],
            'comment' => ['string', 'required', 'min:2', 'max:65535'],
        ];
    }
}
