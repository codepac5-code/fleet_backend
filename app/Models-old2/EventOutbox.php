<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use EventOutbox;

class EventOutbox extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'event_outbox';

    protected $fillable = [
        'uuid',
        'type',
        'channels',
        'payload',
        'status',
        'attempts',
        'available_at',
        'published_at',
    ];

    protected $casts = [
        'channels' => 'array',
        'payload' => 'array',
        'attempts' => 'integer',
        'available_at' => 'datetime',
        'published_at' => 'datetime',
    ];
}
