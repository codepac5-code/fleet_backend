<?php

namespace App\Http\Services\User\Marketplace\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class OfficesSearchRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'route.pickup.lat' => ['required', 'numeric', 'between:-90,90'],
            'route.pickup.lng' => ['required', 'numeric', 'between:-180,180'],
            'route.dropoff.lat' => ['required', 'numeric', 'between:-90,90'],
            'route.dropoff.lng' => ['required', 'numeric', 'between:-180,180'],
            'route.service' => ['sometimes', 'nullable'],
            'route.serviceClass' => ['sometimes', 'nullable'],
            // Price the office cards on the meter (open+km+min) instead of tariff.
            'route.meter' => ['sometimes', 'boolean'],
        ];
    }
}
