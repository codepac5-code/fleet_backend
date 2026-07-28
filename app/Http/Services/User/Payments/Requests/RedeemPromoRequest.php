<?php

namespace App\Http\Services\User\Payments\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class RedeemPromoRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40'],
        ];
    }
}
