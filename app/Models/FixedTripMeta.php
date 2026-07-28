<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Office-acceptance / locked-fare / context / escalation bookkeeping for a
 * FIXED ride_booking. One row per fixed trip; per-shard (country connection).
 */
class FixedTripMeta extends Model
{
    protected $table = 'fixed_trip_meta';

    protected $fillable = [
        'booking_id',
        'sub_service_id',
        'departure_city_id',
        'arrival_city_id',
        'context',
        'company_id',
        'on_behalf_of',
        'locked_fare_minor',
        'currency_code',
        'offer_expires_at',
        'accepted_at',
        'declined_at',
        'decline_reason',
        'offered_office_ids',
        'sla_assign_by',
        'escalated_from_office_id',
    ];

    protected $casts = [
        'sub_service_id' => 'integer',
        'departure_city_id' => 'integer',
        'arrival_city_id' => 'integer',
        'company_id' => 'integer',
        'locked_fare_minor' => 'integer',
        'offered_office_ids' => 'array',
        'offer_expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'sla_assign_by' => 'datetime',
        'escalated_from_office_id' => 'integer',
    ];
}
