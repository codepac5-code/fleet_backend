<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class DriverIncentive extends Model
{
    use ResolvesTenantConnection;

    public const WINDOW_DAY = 'day';
    public const WINDOW_WEEK = 'week';
    public const WINDOW_MONTH = 'month';

    public const WINDOWS = [self::WINDOW_DAY, self::WINDOW_WEEK, self::WINDOW_MONTH];

    protected $table = 'driver_incentives';

    protected $fillable = [
        'name_en', 'name_ar', 'window', 'target_rides', 'reward_minor', 'is_active',
    ];

    protected $casts = [
        'target_rides' => 'integer',
        'reward_minor' => 'integer',
        'is_active' => 'boolean',
    ];
}
