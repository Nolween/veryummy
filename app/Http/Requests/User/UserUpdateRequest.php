<?php

namespace App\Http\Requests\User;

use App\Rules\CheckCurrentPassword;
use App\Rules\PasswordRepetition;
use App\Rules\UserMailExists;
use App\Rules\UserNameExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UserUpdateRequest extends FormRequest
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
     * @return array<string, array<UserMailExists|UserNameExists|CheckCurrentPassword|PasswordRepetition|string>>
     */
    public function rules(): array
    {
        return [
            'email'            => ['email', new UserMailExists],
            'name'             => ['string', new UserNameExists],
            'current-password' => ['string', 'required', new CheckCurrentPassword],
            'password'         => ['string', 'nullable', new PasswordRepetition],
            'confirmation'     => ['string', 'nullable'],
        ];
    }
}
