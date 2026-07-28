<?php

namespace App\Http\Services\User\Marketplace\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class EstimateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'pickup.lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup.lng' => ['required', 'numeric', 'between:-180,180'],
            'dropoff.lat' => ['required', 'numeric', 'between:-90,90'],
            'dropoff.lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
