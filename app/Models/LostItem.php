<?php

namespace App\Models;

use App\Traits\StampsActiveCountry;
use Illuminate\Database\Eloquent\Model;

/**
 * Item left in a vehicle. Backs POST /trips/{id}/lost-item (returns TicketRef).
 * @see migration 2026_07_15_000001_add_rider_api_missing_columns
 */
class LostItem extends Model
{
    use StampsActiveCountry;

    protected $connection = 'global';

    protected $table = 'lost_items';

    protected $fillable = [
        'user_id', 'booking_id', 'reporter_type', 'driver_id', 'office_id',
        'ticket_id', 'category', 'description', 'photo', 'share_masked_number',
        'status', 'matched_item_id', 'resolution', 'matched_at', 'returned_at', 'country_code',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'booking_id' => 'integer',
        'driver_id' => 'integer',
        'office_id' => 'integer',
        'ticket_id' => 'integer',
        'matched_item_id' => 'integer',
        'share_masked_number' => 'boolean',
        'matched_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(RideBooking::class, 'booking_id');
    }

    /** The paired report on the same booking (a lost ↔ found match). */
    public function match()
    {
        return $this->belongsTo(self::class, 'matched_item_id');
    }
}
