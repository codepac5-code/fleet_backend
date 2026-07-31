<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * The permission catalog, pinned to the PLATFORM connection.
 *
 * Spatie resolves permission names through the default connection, but a
 * relation runs on the RELATED model's connection — so an Employee (per shard)
 * wrote its `model_has_permissions` rows into the shard while the ids came from
 * the platform catalog. The two could never join, and every employee outside the
 * reference database resolved to zero permissions no matter what was granted.
 * Binding the relation to this model keeps ids and pivots in one place.
 *
 * @see Employee::permissions()
 */
class PlatformPermission extends SpatiePermission
{
    protected $connection = 'global';
}
