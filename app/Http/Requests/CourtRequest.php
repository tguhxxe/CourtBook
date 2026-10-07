<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100'], 'sport' => ['required', 'in:tennis,soccer'], 'description' => ['required', 'string', 'max:2000'], 'facilities' => ['required', 'string', 'max:1000'], 'hourly_rate' => ['required', 'integer', 'between:1000,10000000'], 'active' => ['required', 'boolean']];
    }
}
