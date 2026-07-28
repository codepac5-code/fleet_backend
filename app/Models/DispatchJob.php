<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class DispatchJob extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'dispatch_jobs';

    protected $fillable = [
        'booking_id',
        'office_id',
        'service_class',
        'lat',
        'lng',
        'status',
        'assigned_driver_id',
        'wave',
        'assigned_at',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'office_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'assigned_driver_id' => 'integer',
        'wave' => 'integer',
        'assigned_at' => 'datetime',
    ];
}
