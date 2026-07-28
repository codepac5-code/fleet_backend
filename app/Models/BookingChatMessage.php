<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class BookingChatMessage extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'booking_chat_messages';

    protected $fillable = ['booking_id', 'from_type', 'body', 'read_at'];

    protected $casts = [
        'booking_id' => 'integer',
        'read_at' => 'datetime',
    ];
}
