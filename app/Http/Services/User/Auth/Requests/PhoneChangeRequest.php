<?php

namespace App\Http\Services\User\Auth\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class PhoneChangeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'dialCode' => ['required', 'string', 'max:8'],
            'phone' => ['required', 'string', 'max:20'],
        ];
    }
}
