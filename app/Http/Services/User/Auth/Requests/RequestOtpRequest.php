<?php

namespace App\Http\Services\User\Auth\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class RequestOtpRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'dialCode' => ['required', 'string', 'max:8'],
            'phone' => ['required', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:4'],
        ];
    }
}
