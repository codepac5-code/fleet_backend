<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * A driver's account-deletion request. Stays `pending` until the linked office
 * confirms — the account is not touched here.
 */
class DriverDeletionRequest extends Model
{
    use ResolvesTenantConnection;

    public $timestamps = false;

    protected $table = 'driver_deletion_requests';

    protected $fillable = ['driver_id', 'status', 'reason', 'created_at'];

    protected $casts = [
        'driver_id' => 'integer',
        'created_at' => 'datetime',
    ];
}
