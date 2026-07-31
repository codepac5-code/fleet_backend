<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-country catalogue: each shard carries its own list, and reading it off
 * the default connection showed a Damascus office the Doha catalogue while
 * its own shard's rows sat unused.
 */
class VehicleModel extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'vehicle_models';

    protected $fillable = ['brand_id', 'name', 'name_en', 'status'];

    protected $casts = [
        'brand_id' => 'integer',
        'status' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(VehicleBrand::class, 'brand_id');
    }
}
