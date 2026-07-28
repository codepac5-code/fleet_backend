<?php

namespace App\Http\Core\Classes\Payment\Gateway;

use Illuminate\Http\Request;

class GenericGateway implements PaymentGateway
{
    public function verifyAndNormalize(Request $request): ?array
    {
        return [
            'handled' => true,
            'idempotency_key' => (string) $request->input('idempotency_key', ''),
            'status' => (string) $request->input('status', ''),
            'provider_ref' => $request->input('provider_ref'),
        ];
    }
}
