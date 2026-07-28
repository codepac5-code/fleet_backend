<?php

namespace App\Http\Services\User\Auth\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class PhoneChangeVerifyRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'challengeId' => ['required', 'string'],
            'code' => ['required', 'string', 'max:8'],
        ];
    }
}
