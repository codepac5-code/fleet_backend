<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Per-country catalogue: each shard carries its own list, and reading it off
 * the default connection showed a Damascus office the Doha catalogue while
 * its own shard's rows sat unused.
 */
class VehicleBrand extends Model
{
    use ResolvesTenantConnection;

    use HasFactory , SoftDeletes;

    protected $fillable = ['name', 'description','image','name_en','description_en','status'];
}
