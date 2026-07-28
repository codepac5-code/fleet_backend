<?php

namespace App\Http\Core\Classes\Payment\Gateway;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function verifyAndNormalize(Request $request): ?array;
}
