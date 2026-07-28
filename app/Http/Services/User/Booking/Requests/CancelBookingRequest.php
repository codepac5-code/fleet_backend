<?php

namespace App\Http\Services\User\Booking\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class CancelBookingRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
