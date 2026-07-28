<?php

namespace App\Http\Services\User\Auth\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class SocialLoginRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:google,apple'],
            'token' => ['required', 'string'],
        ];
    }
}
