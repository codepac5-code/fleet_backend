<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class CommissionSnapshot extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'commission_snapshots';

    protected $fillable = [
        'booking_id',
        'office_id',
        'driver_id',
        'currency_code',
        'pricing_style',
        'fare_minor',
        'discount_minor',
        'total_minor',
        'fleet_rate',
        'office_rate',
        'fleet_minor',
        'office_minor',
        'driver_minor',
        'subscription_plan',
    ];

    protected $casts = [
        'fare_minor' => 'integer',
        'discount_minor' => 'integer',
        'total_minor' => 'integer',
        'fleet_minor' => 'integer',
        'office_minor' => 'integer',
        'driver_minor' => 'integer',
    ];
}
