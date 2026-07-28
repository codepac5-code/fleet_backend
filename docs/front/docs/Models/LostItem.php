<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Item left in a vehicle. Backs POST /trips/{id}/lost-item (returns TicketRef).
 * @see migration 2026_07_15_000001_add_rider_api_missing_columns
 */
class LostItem extends Model
{
    protected $connection = 'global';

    protected $table = 'lost_items';

    protected $fillable = [
        'user_id', 'booking_id', 'ticket_id', 'category',
        'description', 'share_masked_number', 'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'booking_id' => 'integer',
        'ticket_id' => 'integer',
        'share_masked_number' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(RideBooking::class, 'booking_id');
    }
}
