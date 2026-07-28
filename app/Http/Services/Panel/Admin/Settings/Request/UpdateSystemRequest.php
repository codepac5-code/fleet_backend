<?php

namespace App\Http\Services\Panel\Admin\Settings\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => ['required', 'in:ar,en'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:64'],
        ];
    }
}
