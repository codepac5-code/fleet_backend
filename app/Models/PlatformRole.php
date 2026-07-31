<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/** Roles alongside [PlatformPermission], on the platform connection. */
class PlatformRole extends SpatieRole
{
    protected $connection = 'global';
}
