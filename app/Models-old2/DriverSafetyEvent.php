<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use DriverSafetyEvent;

class DriverSafetyEvent extends Model
{
    use ResolvesTenantConnection;

    public $timestamps = false;

    protected $table = 'driver_safety_events';

    protected $fillable = [
        'driver_id', 'booking_id', 'office_id', 'kind', 'category', 'status', 'note', 'lat', 'lng', 'hold_ms', 'created_at',
    ];

    protected $casts = [
        'driver_id' => 'integer',
        'booking_id' => 'integer',
        'office_id' => 'integer',
        'hold_ms' => 'integer',
        'created_at' => 'datetime',
    ];
}
