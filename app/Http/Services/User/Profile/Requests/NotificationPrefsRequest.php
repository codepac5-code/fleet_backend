<?php

namespace App\Http\Services\User\Profile\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class NotificationPrefsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'tripUpdates' => ['sometimes', 'boolean'],
            'promotions' => ['sometimes', 'boolean'],
            'officeMessages' => ['sometimes', 'boolean'],
            'safetyAlerts' => ['sometimes', 'boolean'],
        ];
    }
}
