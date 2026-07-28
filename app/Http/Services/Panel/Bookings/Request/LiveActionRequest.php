<?php

namespace App\Http\Services\Panel\Bookings\Request;

use Illuminate\Foundation\Http\FormRequest;

class LiveActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|string|in:hold,cancel,complete_paid,complete_unpaid',
            'reason' => 'nullable|string|max:350|required_if:action,cancel',
        ];
    }
}
