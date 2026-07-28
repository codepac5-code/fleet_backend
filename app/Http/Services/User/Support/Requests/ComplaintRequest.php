<?php

namespace App\Http\Services\User\Support\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class ComplaintRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'about' => ['required', 'string', 'max:40'],
            'tripId' => ['nullable', 'integer', 'min:1'],
            'description' => ['required', 'string', 'max:2000'],
            'photoUrl' => ['nullable', 'string', 'max:512'],
        ];
    }
}
