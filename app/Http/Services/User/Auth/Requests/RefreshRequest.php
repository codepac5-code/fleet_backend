<?php

namespace App\Http\Services\User\Auth\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class RefreshRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'refreshToken' => ['required', 'string'],
        ];
    }
}
