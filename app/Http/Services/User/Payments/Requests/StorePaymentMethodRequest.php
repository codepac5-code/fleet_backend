<?php

namespace App\Http\Services\User\Payments\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class StorePaymentMethodRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'stripePaymentMethodId' => ['required', 'string', 'max:255'],
            'setDefault' => ['nullable', 'boolean'],
        ];
    }
}
