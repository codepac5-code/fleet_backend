<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class DriverIncentiveProgress extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'driver_incentive_progress';

    protected $fillable = [
        'incentive_id', 'driver_id', 'period', 'rides', 'rewarded', 'reward_minor', 'currency_code', 'rewarded_at',
    ];

    protected $casts = [
        'incentive_id' => 'integer',
        'driver_id' => 'integer',
        'rides' => 'integer',
        'rewarded' => 'boolean',
        'reward_minor' => 'integer',
        'rewarded_at' => 'datetime',
    ];
}
