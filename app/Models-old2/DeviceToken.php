<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use DeviceToken;

class DeviceToken extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'app_device_tokens';

    protected $fillable = ['owner_type', 'owner_id', 'token', 'platform', 'last_seen_at'];

    protected $casts = [
        'owner_id' => 'integer',
        'last_seen_at' => 'datetime',
    ];
}
