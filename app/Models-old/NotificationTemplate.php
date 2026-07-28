<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use NotificationTemplate;

class NotificationTemplate extends Model
{
    protected $connection = 'global';
    protected $table = 'notification_templates';

    protected $fillable = ['key', 'channels', 'subject_i18n', 'body_i18n', 'is_active'];

    protected $casts = [
        'channels' => 'array',
        'subject_i18n' => 'array',
        'body_i18n' => 'array',
        'is_active' => 'boolean',
    ];
}
