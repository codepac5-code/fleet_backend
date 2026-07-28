<?php

namespace App\Http\Services\User\Profile\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class PrivacyRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'locationDuringTrips' => ['sometimes', 'boolean'],
            'shareTripDataWithOffice' => ['sometimes', 'boolean'],
            'marketing' => ['sometimes', 'boolean'],
        ];
    }
}
