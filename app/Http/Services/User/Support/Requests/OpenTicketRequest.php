<?php

namespace App\Http\Services\User\Support\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class OpenTicketRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', 'max:80'],
            'tripId' => ['nullable', 'integer', 'min:1'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }
}
