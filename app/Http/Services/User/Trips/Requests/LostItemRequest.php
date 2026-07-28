<?php

namespace App\Http\Services\User\Trips\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class LostItemRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'shareMaskedNumber' => ['nullable', 'boolean'],
        ];
    }
}
