<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Prices belong to the country the office trades in. Without tenant routing
 * every read landed on the platform database, so an office editing its prices
 * in Syria wrote `fleet_sy` while the rider search kept reading `fleet`.
 */
class OfficeSubServicePrice extends Model
{
    use ResolvesTenantConnection;

    protected $fillable = [
        'office_id',
        'sub_service_id',
        'openPrice',
        'kmPrice',
        'minutePrice',
        'is_enabled',
    ];

    protected $casts = [
        'office_id' => 'integer',
        'sub_service_id' => 'integer',
        'is_enabled' => 'boolean',
    ];

    /**
     * Rows an office actually offers — the rider search must use this.
     *
     * On a shard that has not taken the flag yet every existing row means
     * "offered", so the filter is skipped rather than emptying the search.
     */
    public function scopeOffered($query)
    {
        $connection = $query->getModel()->getConnectionName();

        if (! static::flagExists($connection)) {
            return $query;
        }

        return $query->where('is_enabled', true);
    }

    private static function flagExists(?string $connection): bool
    {
        try {
            return Schema::connection($connection)->hasColumn('office_sub_service_prices', 'is_enabled');
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * A row can mean two different things: "I offer this at MY price" or "I
     * offer this at the catalog price". All-zero rates mean the latter, so they
     * must not be treated as an override — otherwise enabling a service without
     * typing a price would charge the rider zero.
     */
    public function isPriceOverride(): bool
    {
        return ((float) $this->openPrice) > 0 || ((float) $this->kmPrice) > 0 || ((float) $this->minutePrice) > 0;
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function subService()
    {
        return $this->belongsTo(SubService::class);
    }

    
}
