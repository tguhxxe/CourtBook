<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return ['court_id' => ['required', 'integer', 'exists:courts,id'], 'starts_at' => ['required', 'date_format:Y-m-d\\TH:i'], 'ends_at' => ['required', 'date_format:Y-m-d\\TH:i', 'after:starts_at'], 'reason' => ['required', 'string', 'max:200']];
    }
}
