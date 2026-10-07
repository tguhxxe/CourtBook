<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return ['open_hour' => ['required', 'integer', 'between:0,22'], 'close_hour' => ['required', 'integer', 'between:1,23', 'gt:open_hour'], 'dp_percent' => ['required', 'integer', 'between:1,100']];
    }
}
