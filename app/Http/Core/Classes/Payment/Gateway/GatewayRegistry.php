<?php

namespace App\Http\Core\Classes\Payment\Gateway;

use App\Http\Core\Const\Options\PaymentGatewayName;

class GatewayRegistry
{
    public function for(string $provider): ?PaymentGateway
    {
        return match (strtolower($provider)) {
            PaymentGatewayName::$STRIPE => new StripeGateway(),
            PaymentGatewayName::$SYRIATEL, PaymentGatewayName::$MTN, 'manual' => new GenericGateway(),
            default => null,
        };
    }
}
