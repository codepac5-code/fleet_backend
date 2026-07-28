<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'app_notifications';

    protected $fillable = [
        'event_uuid', 'notifiable_type', 'notifiable_id', 'template_key',
        'type', 'locale', 'title', 'body', 'data', 'read_at',
    ];

    protected $casts = [
        'notifiable_id' => 'integer',
        'data' => 'array',
        'read_at' => 'datetime',
    ];
}
