<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'ride_limit',
        'extra_ride_minor',
        'extra_driver_minor',
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
        'ride_limit' => 'integer',
        'extra_ride_minor' => 'integer',
        'extra_driver_minor' => 'integer',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'features' => 'array',
        'sort' => 'integer',
    ];
}
