<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use RideBooking;

class RideBooking extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'ride_bookings';

    protected $fillable = [
        'user_id',
        'office_id',
        'source',
        'created_by',
        'service',
        'service_class',
        'pricing_style',
        'status',
        'pickup_lat',
        'pickup_lng',
        'pickup_note',
        'pickup_title',
        'dropoff_lat',
        'dropoff_lng',
        'dropoff_title',
        'distance_m',
        'duration_s',
        'currency_code',
        'fare_minor',
        'discount_minor',
        'total_minor',
        'held_minor',
        'payment_method',
        'promo_code',
        'idempotency_key',
        'scheduled_at',
        'passengers',
        'luggage',
        'flight_no',
        'assigned_at',
        'completed_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'office_id' => 'integer',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'distance_m' => 'integer',
        'duration_s' => 'integer',
        'fare_minor' => 'integer',
        'discount_minor' => 'integer',
        'total_minor' => 'integer',
        'held_minor' => 'integer',
        'scheduled_at' => 'datetime',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
}
