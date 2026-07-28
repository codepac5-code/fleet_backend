<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class ServiceTariff extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'service_tariffs';

    protected $fillable = [
        'office_id', 'service', 'service_class', 'currency_code', 'pricing_style',
        'base_minor', 'per_km_minor', 'per_minute_minor', 'minimum_minor', 'fixed_minor', 'is_active',
    ];

    protected $casts = [
        'office_id' => 'integer',
        'base_minor' => 'integer',
        'per_km_minor' => 'integer',
        'per_minute_minor' => 'integer',
        'minimum_minor' => 'integer',
        'fixed_minor' => 'integer',
        'is_active' => 'boolean',
    ];
}
