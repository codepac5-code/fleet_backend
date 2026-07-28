<?php

namespace App\Http\Services\User\Scheduled\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class CreateScheduledRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'office_id' => ['required', 'integer', 'min:1'],
            // Present → the ride is meter-priced from this sub-service.
            'sub_service_id' => ['nullable', 'integer', 'min:1'],
            'route' => ['required', 'array'],
            'route.pickup.lat' => ['required', 'numeric', 'between:-90,90'],
            'route.pickup.lng' => ['required', 'numeric', 'between:-180,180'],
            'route.dropoff.lat' => ['required', 'numeric', 'between:-90,90'],
            'route.dropoff.lng' => ['required', 'numeric', 'between:-180,180'],
            'route.service' => ['required', 'string', 'max:40'],
            'route.serviceClass' => ['required', 'string', 'max:60'],
            'route.pickup.title' => ['nullable', 'string', 'max:255'],
            'route.dropoff.title' => ['nullable', 'string', 'max:255'],
            'scheduledFor' => ['required', 'date'],
            'passengers' => ['nullable', 'integer', 'min:1'],
            'luggage' => ['nullable', 'integer', 'min:0'],
            'flightNo' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Reject a null-island (0,0) pickup or dropoff. `between:-90,90` lets 0
     * through, but a booking at 0,0 is a client that never set the route — it
     * must not silently become a ride to the Gulf of Guinea.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $isNull = fn (string $p) => (float) $this->input("route.$p.lat") === 0.0
                && (float) $this->input("route.$p.lng") === 0.0;

            if ($isNull('pickup')) {
                $v->errors()->add('route.pickup', 'A real pickup location is required.');
            }
            if ($isNull('dropoff')) {
                $v->errors()->add('route.dropoff', 'A real destination is required.');
            }
        });
    }
}
