<?php

namespace App\Models;

use App\Traits\StampsActiveCountry;
use App\Traits\StampsBookingOffice;
use Illuminate\Database\Eloquent\Model;

/**
 * Driver / office / safety complaint. Backs POST /complaints.
 * `about=safety` is routed to FleetOS (routed_to=fleetos, priority=urgent).
 * @see migration 2026_07_15_000001_add_rider_api_missing_columns
 */
class Complaint extends Model
{
    use StampsActiveCountry;
    use StampsBookingOffice;

    protected $connection = 'global';

    protected $table = 'complaints';

    protected $fillable = [
        'user_id', 'booking_id', 'office_id', 'about', 'description',
        'photo_url', 'routed_to', 'priority', 'case_ref', 'status', 'country_code',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'booking_id' => 'integer',
        'office_id' => 'integer',
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
