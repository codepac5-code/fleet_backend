<?php

namespace App\Http\Services\User\Profile\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class AutoShareRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
        ];
    }
}
