<?php

namespace App\Http\Services\User\Payments\Requests;

use App\Http\Services\User\Support\ApiFormRequest;

class PaymentIntentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'purpose' => ['required', 'string', 'in:topup,trip,ride'],
            'tripId' => ['nullable', 'integer', 'min:1'],
            'paymentMethodId' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
