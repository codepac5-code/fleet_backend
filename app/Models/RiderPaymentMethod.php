<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderPaymentMethod extends Model
{
    protected $connection = 'global';

    protected $table = 'rider_payment_methods';

    protected $fillable = [
        'user_id', 'type', 'brand', 'last4', 'exp', 'gateway_token',
        'stripe_payment_method_id', 'stripe_setup_intent_id', 'is_default',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_default' => 'boolean',
    ];
}
