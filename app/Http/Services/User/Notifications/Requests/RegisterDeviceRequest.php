<?php

namespace App\Http\Services\User\Notifications\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class RegisterDeviceRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:ios,android,web'],
        ];
    }
}
