<?php

namespace App\Http\Services\User\Booking\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class CreateBookingRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'service' => ['nullable', 'string', 'max:40'],
            'service_class' => ['required', 'string', 'max:60'],
            'sub_service_id' => ['nullable', 'integer', 'min:1'],
            'pricing_style' => ['nullable', 'string', 'max:40'],
            'office_id' => ['required', 'integer', 'min:1'],
            'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
            'pickup_title' => ['nullable', 'string', 'max:255'],
            'pickup_note' => ['nullable', 'string', 'max:255'],
            'dropoff_lat' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_title' => ['nullable', 'string', 'max:255'],
            'stops' => ['nullable', 'array', 'max:5'],
            'stops.*.lat' => ['required_with:stops', 'numeric', 'between:-90,90'],
            'stops.*.lng' => ['required_with:stops', 'numeric', 'between:-180,180'],
            'stops.*.title' => ['nullable', 'string', 'max:255'],
            'distance_m' => ['nullable', 'integer', 'min:0'],
            'duration_s' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['nullable', 'string', 'in:wallet,cash,office_wallet,card'],
            // The confirmed card pre-authorization (PaymentIntent id) — required
            // by the service when payment_method=card.
            'card_authorization_id' => ['nullable', 'string', 'max:255'],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'scheduled_at' => ['nullable', 'date'],
            'idempotency_key' => ['nullable', 'string', 'max:120'],
        ];
    }
}
