<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-country catalogue: each shard carries its own list, and reading it off
 * the default connection showed a Damascus office the Doha catalogue while
 * its own shard's rows sat unused.
 */
class VehicleColor extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'vehicle_colors';

    protected $fillable = ['name', 'name_en', 'hex', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];
}
