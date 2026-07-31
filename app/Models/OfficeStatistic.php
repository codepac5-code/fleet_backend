<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-office counters, per country.
 */
class OfficeStatistic extends Model
{
    use ResolvesTenantConnection;

    //
}
