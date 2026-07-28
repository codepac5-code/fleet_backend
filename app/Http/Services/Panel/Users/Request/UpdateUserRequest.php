<?php

namespace App\Http\Services\Panel\Users\Request;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $connection = TenantConnection::current();
        $users = $connection ? $connection . '.users' : 'users';
        $userId = (int) $this->route('user');

        return [
            'firstName'    => ['required', 'string', 'max:30'],
            'lastName'     => ['required', 'string', 'max:30'],
            'dialCode'     => ['required', 'string', 'max:8'],
            'phoneNumber'  => ['required', 'string', 'max:20', Rule::unique($users, 'phoneNumber')->ignore($userId)->whereNull('deleted_at')],
            'password'     => ['nullable', 'string', 'min:6', 'max:60'],
            'gender'       => ['nullable', 'in:male,female'],
            'referralCode' => ['nullable', 'string', 'max:50'],
            'isActive'     => ['required', 'in:0,1'],
        ];
    }
}
