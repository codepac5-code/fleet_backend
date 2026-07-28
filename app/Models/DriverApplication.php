<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class DriverApplication extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'driver_applications';

    protected $fillable = [
        'phone', 'name', 'first_name', 'last_name', 'gender',
        'city', 'country', 'region', 'address', 'car_owner',
        'vehicle_type', 'license_number', 'license_path',
        'office_id', 'invite_code', 'kind', 'status',
    ];

    protected $casts = [
        'office_id' => 'integer',
        'car_owner' => 'boolean',
    ];
}
