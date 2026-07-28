<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class OfficeSubscription extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'office_subscriptions';

    protected $fillable = [
        'office_id',
        'plan_key',
        'fleet_commission_rate',
        'office_commission_rate',
        'price_minor',
        'currency_code',
        'status',
        'started_at',
        'period_end',
        'trial_ends_at',
        'current_period_end',
        'cancel_at_period_end',
        'provider',
        'provider_customer_id',
        'provider_subscription_id',
    ];

    protected $casts = [
        'office_id' => 'integer',
        'fleet_commission_rate' => 'float',
        'office_commission_rate' => 'float',
        'price_minor' => 'integer',
        'started_at' => 'datetime',
        'period_end' => 'date',
        'trial_ends_at' => 'datetime',
        'current_period_end' => 'datetime',
        'cancel_at_period_end' => 'boolean',
    ];
}
