<?php

namespace App\Http\Services\Panel\Bookings\Request;

use App\Http\Services\Panel\Bookings\Logic\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(array_keys(BookingStatus::settable()))],
        ];
    }
}
