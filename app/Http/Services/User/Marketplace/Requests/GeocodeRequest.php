<?php

namespace App\Http\Services\User\Marketplace\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class GeocodeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
