<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class DriverPresence extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'driver_presence';
    protected $primaryKey = 'driver_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'driver_id',
        'office_id',
        'status',
        'busy_reason',
        'lat',
        'lng',
        'geohash',
        'heartbeat_at',
    ];

    protected $casts = [
        'driver_id' => 'integer',
        'office_id' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'heartbeat_at' => 'datetime',
    ];
}
