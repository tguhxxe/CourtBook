<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)], 'phone' => ['nullable', 'regex:/^[+0-9 ()-]{8,25}$/'], 'current_password' => ['required_with:password', 'current_password'], 'password' => ['nullable', 'confirmed', Password::min(8)]];
    }
}
