<?php

namespace App\Http\Services\User\Profile\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class SafetyContactRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'relation' => ['sometimes', 'nullable', 'string', 'max:32'],
            'primary' => ['sometimes', 'boolean'],
        ];
    }
}
