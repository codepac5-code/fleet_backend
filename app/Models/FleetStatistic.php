<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-country fleet counters.
 */
class FleetStatistic extends Model
{
    use ResolvesTenantConnection;

    use HasFactory;

    protected $table = 'fleet_statistics'; 

    protected $fillable = [
        'total_income',
        'pending_amount',
        'withdrawn_amount',
        'available_amount',
        'drivers_debt',
        'offices_debt',
        'users_count',
        'drivers_count',
        'offices_count',
        'services_count',
        'subServices_count',
    ];
}
