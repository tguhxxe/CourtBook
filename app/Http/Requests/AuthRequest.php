<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getName()) {
            'register.store' => ['name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:users', 'password' => ['required', 'confirmed', Password::min(8)]], 'login.store' => ['email' => 'required|email', 'password' => 'required|string', 'remember' => 'nullable|boolean'], 'password.email' => ['email' => 'required|email'], 'password.update' => ['email' => 'required|email', 'token' => 'required|string', 'password' => ['required', 'confirmed', Password::min(8)]]
        };
    }
}
