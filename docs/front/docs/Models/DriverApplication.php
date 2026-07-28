<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class DriverApplication extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'driver_applications';

    protected $fillable = [
        'phone', 'name', 'city', 'vehicle_type', 'license_number', 'office_id', 'invite_code', 'kind', 'status',
    ];

    protected $casts = [
        'office_id' => 'integer',
    ];
}
