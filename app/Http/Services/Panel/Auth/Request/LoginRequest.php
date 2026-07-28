<?php

namespace App\Http\Services\Panel\Auth\Request;

use App\Http\Core\Request\BaseRequest;

class LoginRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role'     => ['required', 'in:admin,manager,employee'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'region'   => ['required_unless:role,admin'],
            'remember' => ['nullable', 'boolean'],
        ];
    }
}
