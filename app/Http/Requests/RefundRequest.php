<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return ['status' => ['required', 'in:requested,reviewing,processed,rejected'], 'admin_notes' => ['required', 'string', 'max:2000']];
    }
}
