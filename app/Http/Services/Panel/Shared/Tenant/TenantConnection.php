<?php

namespace App\Http\Services\Panel\Shared\Tenant;

use App\Http\Core\GeoServices\ShardContext;

class TenantConnection
{
    public const NAME = 'dynamic';

    public static function current(): ?string
    {
        return ShardContext::current() ? self::NAME : null;
    }

    public static function isActive(): bool
    {
        return ShardContext::current() !== null;
    }
}
