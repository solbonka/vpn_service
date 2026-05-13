<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class WebRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:web_users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
