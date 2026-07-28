<?php

namespace App\Http\Services\User\Trips\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class SendMessageRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
