<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class RiderSupportTicket extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'rider_support_tickets';

    protected $fillable = [
        'user_id', 'booking_id', 'office_id', 'category', 'topic', 'layer', 'subject', 'status', 'last_message_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'booking_id' => 'integer',
        'office_id' => 'integer',
        'last_message_at' => 'datetime',
    ];
}
