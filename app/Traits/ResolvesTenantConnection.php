<?php

namespace App\Traits;

use App\Http\Core\GeoServices\ShardAggregator;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;

trait ResolvesTenantConnection
{
    public function getConnectionName()
    {
        if (ShardAggregator::isActive()) {
            if ($this->exists && ! empty($this->attributes['_shard'])) {
                return ShardAggregator::shardConnection((int) $this->attributes['_shard']);
            }

            return TenantConnection::NAME;
        }

        if (TenantConnection::isActive()) {
            return TenantConnection::NAME;
        }

        return $this->connection;
    }
}
