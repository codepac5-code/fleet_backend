<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SubscriptionPlan;

class SubscriptionPlan extends Model
{
    protected $connection = 'global';
    protected $table = 'subscription_plans';

    protected $fillable = [
        'key',
        'name',
        'price_minor',
        'currency_code',
        'fleet_commission_rate',
        'driver_limit',
        'trial_days',
        'is_active',
        'is_popular',
        'features',
        'sort',
    ];

    protected $casts = [
        'price_minor' => 'integer',
        'fleet_commission_rate' => 'float',
        'driver_limit' => 'integer',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'features' => 'array',
        'sort' => 'integer',
    ];
}
