<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class RideBooking extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'ride_bookings';

    protected $fillable = [
        'user_id',
        'office_id',
        'driver_id',
        'vehicle_id',
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
        'stops',
        'sub_service_id',
        'distance_m',
        'duration_s',
        'meter_distance_m',
        'meter_last_lat',
        'meter_last_lng',
        'trip_started_at',
        'currency_code',
        'fare_minor',
        'discount_minor',
        'waiting_minor',
        'tip_minor',
        'total_minor',
        'held_minor',
        'payment_method',
        'stripe_payment_intent_id',
        'promo_code',
        'coupon_id',
        'idempotency_key',
        'scheduled_at',
        'passengers',
        'luggage',
        'flight_no',
        'assigned_at',
        'arrived_at',
        'completed_at',
        'cancelled_at',
        'rated_at',
        'cancel_reason',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'office_id' => 'integer',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'sub_service_id' => 'integer',
        'distance_m' => 'integer',
        'duration_s' => 'integer',
        'meter_distance_m' => 'integer',
        'meter_last_lat' => 'float',
        'meter_last_lng' => 'float',
        'trip_started_at' => 'datetime',
        'driver_id' => 'integer',
        'vehicle_id' => 'integer',
        'coupon_id' => 'integer',
        'fare_minor' => 'integer',
        'discount_minor' => 'integer',
        'waiting_minor' => 'integer',
        'tip_minor' => 'integer',
        'total_minor' => 'integer',
        'held_minor' => 'integer',
        'stops' => 'array',
        'scheduled_at' => 'datetime',
        'assigned_at' => 'datetime',
        'arrived_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rated_at' => 'datetime',
    ];
}
