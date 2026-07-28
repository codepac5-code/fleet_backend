<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OfficeRequest;

class OfficeRequest extends Model
{
    protected $table = 'office_requests';

    protected $fillable = [
        'office_name', 'contact_name', 'email', 'phone', 'city', 'country', 'website',
        'business_category', 'fleet_size', 'service_type', 'current_tools', 'coverage',
        'license_status', 'timeline', 'notes', 'status',
    ];

    protected $casts = [
        'fleet_size' => 'integer',
    ];
}
