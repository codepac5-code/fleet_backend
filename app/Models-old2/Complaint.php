<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Complaint;
use Driver;
use RideBooking;
use User;

/**
 * Driver / office / safety complaint. Backs POST /complaints.
 * `about=safety` is routed to FleetOS (routed_to=fleetos, priority=urgent).
 * @see migration 2026_07_15_000001_add_rider_api_missing_columns
 */
class Complaint extends Model
{
    protected $connection = 'global';

    protected $table = 'complaints';

    protected $fillable = [
        'user_id', 'booking_id', 'about', 'description',
        'photo_url', 'routed_to', 'priority', 'case_ref', 'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'booking_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(RideBooking::class, 'booking_id');
    }
}
