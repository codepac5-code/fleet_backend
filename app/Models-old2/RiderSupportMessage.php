<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use RiderSupportMessage;

class RiderSupportMessage extends Model
{
    use ResolvesTenantConnection;

    public $timestamps = false;

    protected $table = 'rider_support_messages';

    protected $fillable = ['ticket_id', 'sender_type', 'sender_id', 'body', 'created_at'];

    protected $casts = [
        'ticket_id' => 'integer',
        'sender_id' => 'integer',
        'created_at' => 'datetime',
    ];
}
