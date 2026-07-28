<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Driver-owned emergency contact. Separate from the rider-owned
 * {@see SafetyContact} (global, user_id) — never shared across roles.
 */
class DriverSafetyContact extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'driver_safety_contacts';

    protected $fillable = ['driver_id', 'name', 'phone', 'relation', 'is_primary', 'auto_share'];

    protected $casts = [
        'driver_id' => 'integer',
        'is_primary' => 'boolean',
        'auto_share' => 'boolean',
    ];
}
