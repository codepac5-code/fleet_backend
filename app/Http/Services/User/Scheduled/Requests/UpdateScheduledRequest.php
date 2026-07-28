<?php

namespace App\Http\Services\User\Scheduled\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class UpdateScheduledRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'scheduledFor' => ['nullable', 'date'],
            'passengers' => ['nullable', 'integer', 'min:1'],
            'luggage' => ['nullable', 'integer', 'min:0'],
            'flightNo' => ['nullable', 'string', 'max:20'],
        ];
    }
}
