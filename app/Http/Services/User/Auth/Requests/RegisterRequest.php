<?php

namespace App\Http\Services\User\Auth\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class RegisterRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'challengeId' => ['required', 'string'],
            'firstName' => ['required', 'string', 'max:30'],
            'lastName' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:190'],
            'country' => ['nullable', 'string', 'max:4'],
        ];
    }
}
