<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use DispatchOffer;

class DispatchOffer extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'dispatch_offers';

    protected $fillable = [
        'booking_id',
        'driver_id',
        'wave',
        'status',
        'distance_m',
        'expires_at',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'driver_id' => 'integer',
        'wave' => 'integer',
        'distance_m' => 'integer',
        'expires_at' => 'datetime',
    ];
}
