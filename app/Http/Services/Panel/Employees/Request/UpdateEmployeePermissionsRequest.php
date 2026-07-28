<?php

namespace App\Http\Services\Panel\Employees\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ];
    }

    public function selected(): array
    {
        return array_values(array_unique($this->input('permissions', [])));
    }
}
