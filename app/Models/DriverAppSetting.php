<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-driver app preferences: language, payout/cash toggles, and the last
 * reported OS permission grants. One row per driver (upserted).
 */
class DriverAppSetting extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'driver_app_settings';

    protected $fillable = ['driver_id', 'locale', 'auto_payout', 'accept_cash', 'payout_bank_id', 'permissions'];

    protected $casts = [
        'driver_id' => 'integer',
        'auto_payout' => 'boolean',
        'accept_cash' => 'boolean',
        'permissions' => 'array',
    ];
}
