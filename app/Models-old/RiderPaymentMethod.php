<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RiderPaymentMethod;

class RiderPaymentMethod extends Model
{
    protected $connection = 'global';

    protected $table = 'rider_payment_methods';

    protected $fillable = ['user_id', 'type', 'brand', 'last4', 'exp', 'gateway_token', 'is_default'];

    protected $casts = [
        'user_id' => 'integer',
        'is_default' => 'boolean',
    ];
}
