<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return ['court_id' => ['required', 'integer', 'exists:courts,id'], 'date' => ['required', 'date_format:Y-m-d'], 'hour' => ['required', 'integer', 'between:0,23'], 'duration' => ['required', 'integer', 'between:1,4']];
    }
}
