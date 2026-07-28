<?php

namespace App\Http\Services\User\Trips\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class RateTripRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:60'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'bookAgain' => ['nullable', 'boolean'],
            'favorite' => ['nullable', 'boolean'],
        ];
    }
}
