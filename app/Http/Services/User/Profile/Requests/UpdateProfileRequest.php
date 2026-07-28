<?php

namespace App\Http\Services\User\Profile\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class UpdateProfileRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'firstName' => ['sometimes', 'string', 'max:30'],
            'lastName' => ['sometimes', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:190'],
            'avatarUrl' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'language' => ['sometimes', 'string', 'in:en,ar'],
        ];
    }
}
