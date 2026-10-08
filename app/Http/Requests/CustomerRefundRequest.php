<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer'
            && $this->route('booking')->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:5', 'max:200']];
    }

    public function attributes(): array
    {
        return ['reason' => 'alasan refund'];
    }
}
