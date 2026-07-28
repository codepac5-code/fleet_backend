<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * A shareable live-status tracking link created by the driver
 * (`POST /driver/safety/status-links`). Revoked (not deleted) so the token can
 * never be reused.
 */
class DriverStatusLink extends Model
{
    use ResolvesTenantConnection;

    public $timestamps = false;

    protected $table = 'driver_status_links';

    protected $fillable = ['driver_id', 'booking_id', 'token', 'expires_at', 'revoked_at', 'created_at'];

    protected $casts = [
        'driver_id' => 'integer',
        'booking_id' => 'integer',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
