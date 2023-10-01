<?php

namespace App\Http\Requests\User;

use App\Rules\CheckCurrentPassword;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UserDestroyRequest extends FormRequest
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
     * @return array<string, array<CheckCurrentPassword|string>>
     */
    public function rules(): array
    {
        return [
            'delete-account-password' => ['string', 'nullable', new CheckCurrentPassword],
        ];
    }
}
