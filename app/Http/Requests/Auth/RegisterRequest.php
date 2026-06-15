<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'country' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3', Rule::in(config('currencies.supported'))],
            'role' => ['sometimes', Rule::in([User::ROLE_EMPLOYEE, User::ROLE_FINANCE])],
        ];
    }
}
